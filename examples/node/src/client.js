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
            const lib = path.startsWith('https') ? require('https') : require('http'); // select http or https module, depending on reqested url
            
            const request = lib[method](path, (response) => {
                if (response.statusCode < 200 || response.statusCode > 299) { // handle http errors
                    reject(new Error('Failed to load page, status code: ' + response.statusCode));
                }
                
                const body = [];
                
                response.on('data', (chunk) => body.push(chunk)); // on every content chunk, push it to the data array
                
                response.on('end', () => resolve(body.join(''))); // we are done, resolve promise with those joined chunks
            });
            
            request.on('error', (err) => reject(err)); // handle connection errors of the request
        });
    }
}

module.exports = Client;