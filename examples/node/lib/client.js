const URL = require('url').URL;

class Client {
    
    constructor() {
        this.endpoint = 'https://appwrite.io/v1';
        this.headers = {
            'content-type': '',
            'x-sdk-version': 'appwrite:nodejs:',
        };
        this.selfSigned = false;
    }

    /**
     * Set Project
     *
     * @param string value
     *
     * @return self
     */
    setProject(value) {
        this.addHeader('X-Appwrite-Project', value);

        return this;
    }

    /**
     * Set Key
     *
     * @param string value
     *
     * @return self
     */
    setKey(value) {
        this.addHeader('X-Appwrite-Key', value);

        return this;
    }

    /**
     * Set Locale
     *
     * @param string value
     *
     * @return self
     */
    setLocale(value) {
        this.addHeader('X-Appwrite-Locale', value);

        return this;
    }

    /**
     * Set Mode
     *
     * @param string value
     *
     * @return self
     */
    setMode(value) {
        this.addHeader('X-Appwrite-Mode', value);

        return this;
    }

    /***
     * @param bool status
     * @return this
     */
    setSelfSigned(status = true) {
        this.selfSigned = status;

        return this;
    }

    /***
     * @param endpoint
     * @return this
     */
    setEndpoint(endpoint)
    {
        this.endpoint = endpoint;

        return this;
    }

    /**
     * @param key string
     * @param value string
     */
    addHeader(key, value) {
        this.headers[key.toLowerCase()] = value.toLowerCase();
        
        return this;
    }
    
    call(method, path = '', headers = [], params =[]) {
        if(this.selfSigned) { // Allow self signed requests
            process.env["NODE_TLS_REJECT_UNAUTHORIZED"] = 0;
        }
      
        return new Promise((resolve, reject) => {
            let lib = this.endpoint.startsWith('https') ? require('https') : require('http');
            
            let url = new URL(this.endpoint + path);

            params = JSON.stringify(params);

            headers['content-length'] = params.length;

            let options = {
                'method': method.toUpperCase(),
                'headers': Object.assign(this.headers, headers),
                'protocol': url.protocol,
                'hostname': url.hostname,
                'port': url.port || (this.endpoint.startsWith('https') ? 443 : 80),
                'path': url.pathname,
            }

            var req = lib.request(options, (res) => {
                if (res.statusCode < 200 || res.statusCode > 299) {
                    reject(new Error('Failed to load page, status code: ' + res.statusCode));
                }
                
                let body = '';

                res.on('data', chunk => body += chunk);
                res.on('end', () => resolve(body));
            });

            req.on('error', function (error) {
                reject(error)
            });

            if (method.toLowerCase() != 'get') {
                req.write(params);
            }

            req.end();
        });
    }
}

module.exports = Client;