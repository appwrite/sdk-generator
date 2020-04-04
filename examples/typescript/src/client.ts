import * as request from "request-promise-native";

interface assoc {
    [key: string]: any;
}

export class Client {

    endpoint: string = 'https://appwrite.io/v1';
    headers: assoc = {
        'content-type': '',
        'x-sdk-version': 'appwrite:typescript:',
    };
    
    /**
     * Set Project
     *
     * Your project ID
     *
     * @param string value
     *
     * @return self
     */
    setProject(value: string): this {
        this.addHeader('X-Appwrite-Project', value);

        return this;
    }

    /**
     * Set Key
     *
     * Your secret API key
     *
     * @param string value
     *
     * @return self
     */
    setKey(value: string): this {
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
    setLocale(value: string): this {
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
    setMode(value: string): this {
        this.addHeader('X-Appwrite-Mode', value);

        return this;
    }


    /***
     * @param endpoint
     * @return this
     */
    setEndpoint(endpoint: string): this {
        this.endpoint = endpoint;

        return this;
    }

    /**
     * @param key string
     * @param value string
     */
    addHeader(key: string, value: string): this {
        this.headers[key.toLowerCase()] = value.toLowerCase();
        
        return this;
    }

    async call(method: string, path: string = '', headers: assoc = {}, params: assoc = {}) {
        headers = Object.assign(this.headers, headers);

        let contentType = headers['content-type'].toLowerCase();
        let options = {
            method: method.toUpperCase(),
            uri: this.endpoint + path,
            qs: (method.toUpperCase() === 'GET') ? params : {},
            headers: headers,
            body: (method.toUpperCase() === 'GET' || contentType.startsWith('multipart/form-data')) ? null : params,
            json: (contentType.startsWith('application/json')),
            formData: (contentType.startsWith('multipart/form-data')) ? this.flatten(params) : null,
        };

        let response = await request(options);

        if(contentType.startsWith('multipart/form-data')) {
            response = JSON.parse(response);
        }

        return response;
    }

    flatten(data, prefix = '') {
        let output = {};

        for (const key in data) {
            let value = data[key];
            let finalKey = prefix ? prefix + '[' + key +']' : key;

            if (Array.isArray(value)) {
                output = Object.assign(output, this.flatten(value, finalKey)); // @todo: handle name collision here if needed
            }
            else {
                output[finalKey] = value;
            }
        }

        return output;
    }
}