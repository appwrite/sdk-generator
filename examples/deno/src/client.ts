import { AppwriteException } from './exception.ts';

export interface Payload {
    [key: string]: any;
}

export class Client {
    endpoint: string = 'https://appwrite.io/v1';
    headers: Payload = {
        'content-type': '',
        'x-sdk-version': 'appwrite:deno:0.0.0',
        'X-Appwrite-Response-Format':'0.7.0',
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
     * Set JWT
     *
     * Your secret JSON Web Token
     *
     * @param string value
     *
     * @return self
     */
    setJWT(value: string): this {
        this.addHeader('X-Appwrite-JWT', value);

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
        this.headers[key.toLowerCase()] = value;
        
        return this;
    }

    withoutHeader(key: string, headers: Payload): Payload {
        return Object.keys(headers).reduce((acc: Payload, cv) => {
            if (cv == 'content-type') return acc;
            acc[cv] = headers[cv];
            return acc;
        }, {})
    }

    async call(method: string, path: string = '', headers: Payload = {}, params: Payload = {}) {
        headers = { ...this.headers, ...headers };

        let body;
        const url = new URL(this.endpoint + path);
        if (method.toUpperCase() === 'GET') {
            url.search = new URLSearchParams(this.flatten(params)).toString();
            body = null;
        } else if (headers['content-type'].toLowerCase().startsWith('multipart/form-data')) {
            headers = this.withoutHeader('content-type', headers);
            const formData = new FormData();
            const flatParams = this.flatten(params);
            for (const key in flatParams) {
                formData.append(key, flatParams[key]);
            }
            body = formData;
        } else {
            body = JSON.stringify(params);
        }
        
        const options = {
            method: method.toUpperCase(),
            headers: headers,
            body: body,
        };

        try {
            let response = await fetch(url.toString(), options);
            const contentType = response.headers.get('content-type');

            if (contentType && contentType.includes('application/json')) {
                if(response.status >= 400) {
                    let res = await response.json();
                    throw new AppwriteException(res.message, res.status, res);
                }

                return response.json();
            } else {
                if(response.status >= 400) {
                    let res = await response.text();
                    throw new AppwriteException(res, response.status, null);
                }
                return response;
            }
        } catch(error) {
            throw new AppwriteException(error?.response?.message || error.message, error?.response?.code, error.response);
        }
    }

    flatten(data: Payload, prefix = '') {
        let output: Payload = {};

        for (const key in data) {
            let value = data[key];
            let finalKey = prefix ? prefix + '[' + key +']' : key;

            if (Array.isArray(value)) {
                output = { ...output, ...this.flatten(value, finalKey) }; // @todo: handle name collision here if needed
            }
            else {
                output[finalKey] = value;
            }
        }

        return output;
    }
}