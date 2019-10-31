using RestSharp;
using System.Collections.Generic;

public class Client
{
    protected string _endpoint;
    protected bool _selfSigned;
    protected Dictionary<string, string> _globalHeaders;

    public Client()
    {
        _endpoint = "https://appwrite.io/v1";
        _globalHeaders = new Dictionary<string, string> { "content-type"="", "x-sdk-version"= "appwrite:csharp:" };
        _selfSigned = false;
    }

    public SetSelfSigned(bool status = true)
    {
        _selfSigned = status;
    }

    public SetEndpoint(string endpoint)
    {
        _endpoint = endpoint;
    }

    public AddHeader(string key, string value)
    {
        if (_globalHeaders.ContainsKey(key.ToLower()))
        {
            _globalHeaders[key.ToLower()] = value.ToLower();
        }
        else
        {
            _globalHeaders.Add(key, value.ToLower());
        }
    }

    public SetProject(string value)
    {
        if (_globalHeaders.ContainsKey(key.ToLower()))
        {
            _globalHeaders["x-appwrite-project"] = value.ToLower();
        }
        else
        {
            _globalHeaders.Add("x-appwrite-project", value.ToLower());
        }
    }

    public SetKey(string value)
    {
        if (_globalHeaders.ContainsKey(key.ToLower()))
        {
            _globalHeaders["x-appwrite-key"] = value.ToLower();
        }
        else
        {
            _globalHeaders.Add("x-appwrite-key", value.ToLower());
        }
    }

    public SetLocale(string value)
    {
        if (_globalHeaders.ContainsKey(key.ToLower()))
        {
            _globalHeaders["x-appwrite-locale"] = value.ToLower();
        }
        else
        {
            _globalHeaders.Add("x-appwrite-locale", value.ToLower());
        }
    }

    public SetMode(string value)
    {
        if (_globalHeaders.ContainsKey(key.ToLower()))
        {
            _globalHeaders["x-appwrite-mode"] = value.ToLower();
        }
        else
        {
            _globalHeaders.Add("x-appwrite-mode", value.ToLower());
        }
    }

    public Call(string method, string path="", Dictionary<string, string> headers = null, Dictionary<string, string> paramsIn = null)
    {
        var restClient = new RestClient(_endpoint);
        var request = new RestRequest(path)
        {
            Method = method
        };

        if (headers != null && headers.Count > 0)
        {
            foreach (KeyValuePair<string, string> kvp in headers)
            {
                if (_globalHeaders.ContainsKey(key.ToLower()))
                {
                    _globalHeaders[kvp.Key] = value.ToLower();
                }
                else
                {
                    _globalHeaders.Add(kvp.Key, value.ToLower());
                }
            }

            foreach (KeyValuePair<string, string> kvp in _globalHeaders)
            {
                request.AddHeader(kvp.Key, kvp.Value);
            }
        }

        if (paramsIn == null)
        {
            paramsIn = new Dictionary<string, string>();
        }
        
        if (headers["Content-Type"] == "application/json")
        {
            request.AddJsonBody(paramsIn);
        }
        else
        {
            if (method != "get")
            {
                request.AddObject(paramsIn);
            }
        }

        var response = await client.ExecuteTaskAsync(request);

        return response;
    }
}