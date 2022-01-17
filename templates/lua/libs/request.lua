local http = require('socket.http')
local https = require('ssl.https')
local url_parser = require('socket.url')
local ltn12 = require('ltn12')
local json = require('cjson.safe')
local xml = require('xml')
local md5sum = require('md5')
local mime = require('mime')

local requests = {
  _DESCRIPTION = 'Http requests made simpler',
  http = http,
  https = https
}

local _requests = {}

function requests.HTTPDigestAuth(user, password)
  return { _type = 'digest', user = user, password = password}
end

function requests.HTTPBasicAuth(user, password)
  return { _type = 'basic', user = user, password = password}
end

function requests.post(url, args)
  return requests.request("POST", url, args)
end

function requests.get(url, args)
  return requests.request("GET", url, args)
end

function requests.delete(url, args)
  return requests.request("DELETE", url, args)
end

function requests.patch(url, args)
  return requests.request("PATCH", url, args)
end

function requests.put(url, args)
  return requests.request("PUT", url, args)
end

function requests.options(url, args)
  return requests.request("OPTIONS", url, args)
end

function requests.head(url, args)
  return requests.request("HEAD", url, args)
end

function requests.trace(url, args)
  return requests.request("TRACE", url, args)
end

function requests.request(method, url, args)
  local request

  if type(url) == "table" then
    request = url
    if not request.url and request[1] then
      request.url = table.remove(request, 1)
    end
  else
    request = args or {}
    request.url = url
  end

  request.method = method
  _requests.parse_args(request)

  if request.auth and request.auth._type == 'digest' then
    local response = _requests.make_request(request)
    return _requests.use_digest(response, request)
  else
    return _requests.make_request(request)
  end
end

function _requests.make_request(request)
  local response_body = {}
  local full_request = {
    method = request.method,
    url = request.url,
    headers = request.headers,
    sink = ltn12.sink.table(response_body),
    redirect = request.allow_redirects,
    proxy = request.proxy
  }
  if request.data then
    full_request.source = ltn12.source.string(request.data)
  end

  local response = {}
  local ok
  local socket = string.find(full_request.url, '^https:') and not request.proxy and https or http

  ok, response.status_code, response.headers, response.status = socket.request(full_request)

  assert(ok, 'error in '..request.method..' request: '..response.status_code)
  response.text = table.concat(response_body)
  response.json = function () return json.decode(response.text) end
  response.xml = function () return xml.load(response.text) end

  return response
end

function _requests.parse_args(request)
  _requests.check_url(request)
  _requests.check_data(request)
  _requests.create_header(request)
  _requests.check_timeout(request.timeout)
  _requests.check_redirect(request.allow_redirects)
end

function _requests.format_params(url, params) 
  if not params or next(params) == nil then return url end

  url = url..'?'
  for key, value in pairs(params) do
    if tostring(value) then
      url = url..tostring(key)..'='

      if type(value) == 'table' then
        local val_string = ''

        for _, val in ipairs(value) do
          val_string = val_string..tostring(val)..','
        end

        url = url..val_string:sub(0, -2)
      else
        url = url..tostring(value)
      end

      url = url..'&'
    end
  end

  return url:sub(0, -2)
end

function _requests.check_url(request)
  assert(request.url, 'No url specified for request')
  request.url = _requests.format_params(request.url, request.params)
end


function _requests.create_header(request)
  request.headers = request.headers or {}
  if request.data then
    request.headers['Content-Length'] = request.data:len()
  end

  if request.cookies then
    if request.headers.cookie then
      request.headers.cookie = request.headers.cookie..'; '..request.cookies
    else
      request.headers.cookie = request.cookies
    end
  end

  if request.auth then
    _requests.add_auth_headers(request)
  end
end

function _requests.check_data(request)
  if type(request.data) == "table" then
    request.data = json.encode(request.data)
  elseif request.data then
    request.data = tostring(request.data)
  end
end

function _requests.check_timeout(timeout)
  http.TIMEOUT = timeout or 5
  https.TIMEOUT = timeout or 5
end

function _requests.check_redirect(allow_redirects)
  if allow_redirects and type(allow_redirects) ~= "boolean" then
    error("allow_redirects expects a boolean value. received type = "..type(allow_redirects))
  end
end


function _requests.basic_auth_header(request)
  local encoded = mime.b64(request.auth.user..':'..request.auth.password)
  request.headers.Authorization = 'Basic '..encoded
end


function _requests.digest_create_header_string(auth)
  local authorization = 'Digest username="'..auth.user..'", realm="'..auth.realm..'", nonce="'..auth.nonce
  authorization = authorization..'", uri="'..auth.uri..'", qop='..auth.qop..', nc='..auth.nc
  authorization = authorization..', cnonce="'..auth.cnonce..'", response="'..auth.response..'"'

  if auth.opaque then
    authorization = authorization..', opaque="'..auth.opaque..'"'
  end

  return authorization
end


local function md5_hash(...)
  return md5sum.sumhexa(table.concat({...}, ":"))
end


function _requests.digest_hash_response(auth_table)
  return md5_hash(
    md5_hash(auth_table.user, auth_table.realm, auth_table.password),
    auth_table.nonce,
    auth_table.nc,
    auth_table.cnonce,
    auth_table.qop,
    md5_hash(auth_table.method, auth_table.uri)
  )
end


function _requests.digest_auth_header(request)
  if not request.auth.nonce then return end

  request.auth.cnonce = request.auth.cnonce or string.format("%08x", os.time())

  request.auth.nc_count = request.auth.nc_count or 0
  request.auth.nc_count = request.auth.nc_count + 1

  request.auth.nc = string.format("%08x", request.auth.nc_count)

  local url = url_parser.parse(request.url)
  request.auth.uri = url_parser.build{path = url.path, query = url.query}
  request.auth.method = request.method
  request.auth.qop = 'auth'

  request.auth.response = _requests.digest_hash_response(request.auth)

  request.headers.Authorization = _requests.digest_create_header_string(request.auth)
end



function _requests.use_digest(response, request)
  if response.status_code == 401 then
    _requests.parse_digest_response_header(response,request)
    _requests.create_header(request)
    response = _requests.make_request(request)
    response.auth = request.auth
    response.cookies = request.headers.cookie
    return response
  else
    response.auth = request.auth
    response.cookies = request.headers.cookie
    return response
  end
end


function _requests.parse_digest_response_header(response, request)
  for key, value in response.headers['www-authenticate']:gmatch('(%w+)="(%S+)"') do
    request.auth[key] = value
  end

  if request.headers.cookie then
    request.headers.cookie = request.headers.cookie..'; '..response.headers['set-cookie']
  else
    request.headers.cookie = response.headers['set-cookie']
  end

  request.auth.nc_count = 0
end


function _requests.add_auth_headers(request)
  local auth_func = {
    basic = _requests.basic_auth_header,
    digest = _requests.digest_auth_header
  }

  auth_func[request.auth._type](request)
end


requests._private = _requests
return requests