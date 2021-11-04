import 'isomorphic-form-data';
import { fetch } from 'cross-fetch';

type Payload = {
    [key: string]: any;
}

type Headers = {
    [key: string]: string;
}

type RealtimeResponse = {
    type: 'error'|'event'|'connected'|'response';
    data: RealtimeResponseAuthenticated|RealtimeResponseConnected|RealtimeResponseError|RealtimeResponseEvent<unknown>;
}

type RealtimeRequest = {
    type: 'authentication';
    data: RealtimeRequestAuthenticate;
}

type RealtimeResponseEvent<T extends unknown> = {
    event: string;
    channels: string[];
    timestamp: number;
    payload: T;
}

type RealtimeResponseError = {
    code: number;
    message: string;
}

type RealtimeResponseConnected = {
    channels: string[];
    user?: object;
}

type RealtimeResponseAuthenticated = {
    to: string;
    success: boolean;
    user: object;
}

type RealtimeRequestAuthenticate = {
    session: string;
}

type Realtime = {
    socket?: WebSocket;
    timeout?: number;
    url?: string;
    lastMessage?: RealtimeResponse;
    channels: Set<string>;
    subscriptions: Map<number, {
        channels: string[];
        callback: (payload: RealtimeResponseEvent<any>) => void
    }>;
    subscriptionsCounter: number;
    reconnect: boolean;
    reconnectAttempts: number;
    getTimeout: () => number;
    connect: () => void;
    createSocket: () => void;
    cleanUp: (channels: string[]) => void;
    onMessage: (event: MessageEvent) => void;
}

class AppwriteException extends Error {
    code: number;
    response: string;
    constructor(message: string, code: number = 0, response: string = '') {
        super(message);
        this.name = 'AppwriteException';
        this.message = message;
        this.code = code;
        this.response = response;
    }
}

class Appwrite {
    config = {
        endpoint: 'https://appwrite.io/v1',
        endpointRealtime: '',
        project: '',
        key: '',
        jwt: '',
        locale: '',
    };
    headers: Headers = {
        'x-sdk-version': 'appwrite:web:0.0.0',
        'X-Appwrite-Response-Format': '0.7.0',
    };

    /**
     * Set Endpoint
     *
     * Your project endpoint
     *
     * @param {string} endpoint
     *
     * @returns {this}
     */
    setEndpoint(endpoint: string): this {
        this.config.endpoint = endpoint;
        this.config.endpointRealtime = this.config.endpointRealtime || this.config.endpoint.replace('https://', 'wss://').replace('http://', 'ws://');

        return this;
    }

    /**
     * Set Realtime Endpoint
     *
     * @param {string} endpointRealtime
     *
     * @returns {this}
     */
    setEndpointRealtime(endpointRealtime: string): this {
        this.config.endpointRealtime = endpointRealtime;

        return this;
    }

    /**
     * Set Project
     *
     * Your project ID
     *
     * @param value string
     *
     * @return {this}
     */
    setProject(value: string): this {
        this.headers['X-Appwrite-Project'] = value;
        this.config.project = value;
        return this;
    }

    /**
     * Set Key
     *
     * Your secret API key
     *
     * @param value string
     *
     * @return {this}
     */
    setKey(value: string): this {
        this.headers['X-Appwrite-Key'] = value;
        this.config.key = value;
        return this;
    }

    /**
     * Set JWT
     *
     * Your secret JSON Web Token
     *
     * @param value string
     *
     * @return {this}
     */
    setJWT(value: string): this {
        this.headers['X-Appwrite-JWT'] = value;
        this.config.jwt = value;
        return this;
    }

    /**
     * Set Locale
     *
     * @param value string
     *
     * @return {this}
     */
    setLocale(value: string): this {
        this.headers['X-Appwrite-Locale'] = value;
        this.config.locale = value;
        return this;
    }


    private realtime: Realtime = {
        socket: undefined,
        timeout: undefined,
        url: '',
        channels: new Set(),
        subscriptions: new Map(),
        subscriptionsCounter: 0,
        reconnect: true,
        reconnectAttempts: 0,
        lastMessage: undefined,
        connect: () => {
            clearTimeout(this.realtime.timeout);
            this.realtime.timeout = window?.setTimeout(() => {
                this.realtime.createSocket();
            }, 50);
        },
        getTimeout: () => {
            switch (true) {
                case this.realtime.reconnectAttempts < 5:
                    return 1000;
                case this.realtime.reconnectAttempts < 15:
                    return 5000;
                case this.realtime.reconnectAttempts < 100:
                    return 10_000;
                default:
                    return 60_000;
            }
        },
        createSocket: () => {
            if (this.realtime.channels.size < 1) return;

            const channels = new URLSearchParams();
            channels.set('project', this.config.project);
            this.realtime.channels.forEach(channel => {
                channels.append('channels[]', channel);
            });

            const url = this.config.endpointRealtime + '/realtime?' + channels.toString();

            if (
                url !== this.realtime.url || // Check if URL is present
                !this.realtime.socket || // Check if WebSocket has not been created
                this.realtime.socket?.readyState > WebSocket.OPEN // Check if WebSocket is CLOSING (3) or CLOSED (4)
            ) {
                if (
                    this.realtime.socket &&
                    this.realtime.socket?.readyState < WebSocket.CLOSING // Close WebSocket if it is CONNECTING (0) or OPEN (1)
                ) {
                    this.realtime.reconnect = false;
                    this.realtime.socket.close();
                }

                this.realtime.url = url;
                this.realtime.socket = new WebSocket(url);
                this.realtime.socket.addEventListener('message', this.realtime.onMessage);
                this.realtime.socket.addEventListener('open', _event => {
                    this.realtime.reconnectAttempts = 0;
                });
                this.realtime.socket.addEventListener('close', event => {
                    if (
                        !this.realtime.reconnect ||
                        (
                            this.realtime?.lastMessage?.type === 'error' && // Check if last message was of type error
                            (<RealtimeResponseError>this.realtime?.lastMessage.data).code === 1008 // Check for policy violation 1008
                        )
                    ) {
                        this.realtime.reconnect = true;
                        return;
                    }

                    const timeout = this.realtime.getTimeout();
                    console.error(`Realtime got disconnected. Reconnect will be attempted in ${timeout / 1000} seconds.`, event.reason);

                    setTimeout(() => {
                        this.realtime.reconnectAttempts++;
                        this.realtime.createSocket();
                    }, timeout);
                })
            }
        },
        onMessage: (event) => {
            try {
                const message: RealtimeResponse = JSON.parse(event.data);
                this.realtime.lastMessage = message;
                switch (message.type) {
                    case 'connected':
                        const cookie = JSON.parse(window.localStorage.getItem('cookieFallback') ?? '{}');
                        const session = cookie?.[`a_session_${this.config.project}`];
                        const messageData = <RealtimeResponseConnected>message.data;

                        if (session && !messageData.user) {
                            this.realtime.socket?.send(JSON.stringify(<RealtimeRequest>{
                                type: 'authentication',
                                data: {
                                    session
                                }
                            }));
                        }
                        break;
                    case 'event':
                        let data = <RealtimeResponseEvent<unknown>>message.data;
                        if (data?.channels) {
                            const isSubscribed = data.channels.some(channel => this.realtime.channels.has(channel));
                            if (!isSubscribed) return;
                            this.realtime.subscriptions.forEach(subscription => {
                                if (data.channels.some(channel => subscription.channels.includes(channel))) {
                                    setTimeout(() => subscription.callback(data));
                                }
                            })
                        }
                        break;
                    case 'error':
                        throw message.data;
                    default:
                        break;
                }
            } catch (e) {
                console.error(e);
            }
        },
        cleanUp: channels => {
            this.realtime.channels.forEach(channel => {
                if (channels.includes(channel)) {
                    let found = Array.from(this.realtime.subscriptions).some(([_key, subscription] )=> {
                        return subscription.channels.includes(channel);
                    })

                    if (!found) {
                        this.realtime.channels.delete(channel);
                    }
                }
            })
        }
    }

    /**
     * Subscribes to Appwrite events and passes you the payload in realtime.
     * 
     * @param {string|string[]} channels 
     * Channel to subscribe - pass a single channel as a string or multiple with an array of strings.
     * 
     * Possible channels are:
     * - account
     * - collections
     * - collections.[ID]
     * - collections.[ID].documents
     * - documents
     * - documents.[ID]
     * - files
     * - files.[ID]
     * - executions
     * - executions.[ID]
     * - functions.[ID]
     * - teams
     * - teams.[ID]
     * - memberships
     * - memberships.[ID]
     * @param {(payload: RealtimeMessage) => void} callback Is called on every realtime update.
     * @returns {() => void} Unsubscribes from events.
     */
    subscribe<T extends unknown>(channels: string | string[], callback: (payload: RealtimeResponseEvent<T>) => void): () => void {
        let channelArray = typeof channels === 'string' ? [channels] : channels;
        channelArray.forEach(channel => this.realtime.channels.add(channel));

        const counter = this.realtime.subscriptionsCounter++;
        this.realtime.subscriptions.set(counter, {
            channels: channelArray,
            callback
        });

        this.realtime.connect();

        return () => {
            this.realtime.subscriptions.delete(counter);
            this.realtime.cleanUp(channelArray);
            this.realtime.connect();
        }
    }

    private async call(method: string, url: URL, headers: Headers = {}, params: Payload = {}): Promise<any> {
        method = method.toUpperCase();
        headers = {
            ...headers,
            ...this.headers
        }
        let options: RequestInit = {
            method,
            headers,
            credentials: 'include'
        };

        if (typeof window !== 'undefined' && window.localStorage) {
            headers['X-Fallback-Cookies'] = window.localStorage.getItem('cookieFallback') ?? '';
        }

        if (method === 'GET') {
            for (const [key, value] of Object.entries(this.flatten(params))) {
                url.searchParams.append(key, value);
            }
        } else {
            switch (headers['content-type']) {
                case 'application/json':
                    options.body = JSON.stringify(params);
                    break;

                case 'multipart/form-data':
                    let formData = new FormData();

                    for (const key in params) {
                        if (Array.isArray(params[key])) {
                            params[key].forEach((value: any) => {
                                formData.append(key + '[]', value);
                            })
                        } else {
                            formData.append(key, params[key]);
                        }
                    }

                    options.body = formData;
                    delete headers['content-type'];
                    break;
            }
        }

        try {
            let data = null;
            const response = await fetch(url.toString(), options);

            if (response.headers.get('content-type')?.includes('application/json')) {
                data = await response.json();
            } else {
                data = {
                    message: await response.text()
                };
            }

            if (400 <= response.status) {
                throw new AppwriteException(data?.message, response.status, data);
            }

            const cookieFallback = response.headers.get('X-Fallback-Cookies');

            if (typeof window !== 'undefined' && window.localStorage && cookieFallback) {
                window.console.warn('Appwrite is using localStorage for session management. Increase your security by adding a custom domain as your API endpoint.');
                window.localStorage.setItem('cookieFallback', cookieFallback);
            }

            return data;
        } catch (e) {
            if (e instanceof AppwriteException) {
                throw e;
            }
            throw new AppwriteException((<Error>e).message);
        }
    }

    private flatten(data: Payload, prefix = ''): Payload {
        let output: Payload = {};

        for (const key in data) {
            let value = data[key];
            let finalKey = prefix ? `${prefix}[${key}]` : key;

            if (Array.isArray(value)) {
                output = Object.assign(output, this.flatten(value, finalKey));
            }
            else {
                output[finalKey] = value;
            }
        }

        return output;
    }

    account = {

        /**
         * Get Account
         *
         * Get currently logged in user data as JSON object.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        get: async <T extends unknown>(): Promise<T> => {
            let path = '/account';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Account
         *
         * Delete a currently logged in user account. Behind the scene, the user
         * record is not deleted but permanently blocked from any access. This is done
         * to avoid deleted accounts being overtaken by new users with the same email
         * address. Any user-related resources like documents or storage files should
         * be deleted separately.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        delete: async <T extends unknown>(): Promise<T> => {
            let path = '/account';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Account Email
         *
         * Update currently logged in user account email address. After changing user
         * address, user confirmation status is being reset and a new confirmation
         * mail is sent. For security measures, user password is required to complete
         * this request.
         * This endpoint can also be used to convert an anonymous account to a normal
         * one, by passing an email address and a new password.
         *
         * @param {string} email
         * @param {string} password
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateEmail: async <T extends unknown>(email: string, password: string): Promise<T> => {
            if (typeof email === 'undefined') {
                throw new AppwriteException('Missing required parameter: "email"');
            }

            if (typeof password === 'undefined') {
                throw new AppwriteException('Missing required parameter: "password"');
            }

            let path = '/account/email';
            let payload: Payload = {};

            if (typeof email !== 'undefined') {
                payload['email'] = email;
            }

            if (typeof password !== 'undefined') {
                payload['password'] = password;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Account Logs
         *
         * Get currently logged in user list of latest security activity logs. Each
         * log returns user IP address, location and date and time of log.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getLogs: async <T extends unknown>(): Promise<T> => {
            let path = '/account/logs';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Account Name
         *
         * Update currently logged in user account name.
         *
         * @param {string} name
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateName: async <T extends unknown>(name: string): Promise<T> => {
            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            let path = '/account/name';
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Account Password
         *
         * Update currently logged in user password. For validation, user is required
         * to pass in the new password, and the old password. For users created with
         * OAuth and Team Invites, oldPassword is optional.
         *
         * @param {string} password
         * @param {string} oldPassword
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updatePassword: async <T extends unknown>(password: string, oldPassword?: string): Promise<T> => {
            if (typeof password === 'undefined') {
                throw new AppwriteException('Missing required parameter: "password"');
            }

            let path = '/account/password';
            let payload: Payload = {};

            if (typeof password !== 'undefined') {
                payload['password'] = password;
            }

            if (typeof oldPassword !== 'undefined') {
                payload['oldPassword'] = oldPassword;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Account Preferences
         *
         * Get currently logged in user preferences as a key-value object.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getPrefs: async <T extends unknown>(): Promise<T> => {
            let path = '/account/prefs';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Account Preferences
         *
         * Update currently logged in user account preferences. You can pass only the
         * specific settings you wish to update.
         *
         * @param {object} prefs
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updatePrefs: async <T extends unknown>(prefs: object): Promise<T> => {
            if (typeof prefs === 'undefined') {
                throw new AppwriteException('Missing required parameter: "prefs"');
            }

            let path = '/account/prefs';
            let payload: Payload = {};

            if (typeof prefs !== 'undefined') {
                payload['prefs'] = prefs;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Password Recovery
         *
         * Sends the user an email with a temporary secret key for password reset.
         * When the user clicks the confirmation link he is redirected back to your
         * app password reset URL with the secret key and email address values
         * attached to the URL query string. Use the query string params to submit a
         * request to the [PUT
         * /account/recovery](/docs/client/account#accountUpdateRecovery) endpoint to
         * complete the process. The verification link sent to the user's email
         * address is valid for 1 hour.
         *
         * @param {string} email
         * @param {string} url
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createRecovery: async <T extends unknown>(email: string, url: string): Promise<T> => {
            if (typeof email === 'undefined') {
                throw new AppwriteException('Missing required parameter: "email"');
            }

            if (typeof url === 'undefined') {
                throw new AppwriteException('Missing required parameter: "url"');
            }

            let path = '/account/recovery';
            let payload: Payload = {};

            if (typeof email !== 'undefined') {
                payload['email'] = email;
            }

            if (typeof url !== 'undefined') {
                payload['url'] = url;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Password Recovery (confirmation)
         *
         * Use this endpoint to complete the user account password reset. Both the
         * **userId** and **secret** arguments will be passed as query parameters to
         * the redirect URL you have provided when sending your request to the [POST
         * /account/recovery](/docs/client/account#accountCreateRecovery) endpoint.
         * 
         * Please note that in order to avoid a [Redirect
         * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
         * the only valid redirect URLs are the ones from domains you have set when
         * adding your platforms in the console interface.
         *
         * @param {string} userId
         * @param {string} secret
         * @param {string} password
         * @param {string} passwordAgain
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateRecovery: async <T extends unknown>(userId: string, secret: string, password: string, passwordAgain: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof secret === 'undefined') {
                throw new AppwriteException('Missing required parameter: "secret"');
            }

            if (typeof password === 'undefined') {
                throw new AppwriteException('Missing required parameter: "password"');
            }

            if (typeof passwordAgain === 'undefined') {
                throw new AppwriteException('Missing required parameter: "passwordAgain"');
            }

            let path = '/account/recovery';
            let payload: Payload = {};

            if (typeof userId !== 'undefined') {
                payload['userId'] = userId;
            }

            if (typeof secret !== 'undefined') {
                payload['secret'] = secret;
            }

            if (typeof password !== 'undefined') {
                payload['password'] = password;
            }

            if (typeof passwordAgain !== 'undefined') {
                payload['passwordAgain'] = passwordAgain;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('put', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Account Sessions
         *
         * Get currently logged in user list of active sessions across different
         * devices.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getSessions: async <T extends unknown>(): Promise<T> => {
            let path = '/account/sessions';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete All Account Sessions
         *
         * Delete all sessions from the user account and remove any sessions cookies
         * from the end client.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteSessions: async <T extends unknown>(): Promise<T> => {
            let path = '/account/sessions';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Session By ID
         *
         * Use this endpoint to get a logged in user's session using a Session ID.
         * Inputting 'current' will return the current session being used.
         *
         * @param {string} sessionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getSession: async <T extends unknown>(sessionId: string): Promise<T> => {
            if (typeof sessionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "sessionId"');
            }

            let path = '/account/sessions/{sessionId}'.replace('{sessionId}', sessionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Account Session
         *
         * Use this endpoint to log out the currently logged in user from all their
         * account sessions across all of their different devices. When using the
         * option id argument, only the session unique ID provider will be deleted.
         *
         * @param {string} sessionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteSession: async <T extends unknown>(sessionId: string): Promise<T> => {
            if (typeof sessionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "sessionId"');
            }

            let path = '/account/sessions/{sessionId}'.replace('{sessionId}', sessionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Email Verification
         *
         * Use this endpoint to send a verification message to your user email address
         * to confirm they are the valid owners of that address. Both the **userId**
         * and **secret** arguments will be passed as query parameters to the URL you
         * have provided to be attached to the verification email. The provided URL
         * should redirect the user back to your app and allow you to complete the
         * verification process by verifying both the **userId** and **secret**
         * parameters. Learn more about how to [complete the verification
         * process](/docs/client/account#accountUpdateVerification). The verification
         * link sent to the user's email address is valid for 7 days.
         * 
         * Please note that in order to avoid a [Redirect
         * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
         * the only valid redirect URLs are the ones from domains you have set when
         * adding your platforms in the console interface.
         * 
         *
         * @param {string} url
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createVerification: async <T extends unknown>(url: string): Promise<T> => {
            if (typeof url === 'undefined') {
                throw new AppwriteException('Missing required parameter: "url"');
            }

            let path = '/account/verification';
            let payload: Payload = {};

            if (typeof url !== 'undefined') {
                payload['url'] = url;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Email Verification (confirmation)
         *
         * Use this endpoint to complete the user email verification process. Use both
         * the **userId** and **secret** parameters that were attached to your app URL
         * to verify the user email ownership. If confirmed this route will return a
         * 200 status code.
         *
         * @param {string} userId
         * @param {string} secret
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateVerification: async <T extends unknown>(userId: string, secret: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof secret === 'undefined') {
                throw new AppwriteException('Missing required parameter: "secret"');
            }

            let path = '/account/verification';
            let payload: Payload = {};

            if (typeof userId !== 'undefined') {
                payload['userId'] = userId;
            }

            if (typeof secret !== 'undefined') {
                payload['secret'] = secret;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('put', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

    avatars = {

        /**
         * Get Browser Icon
         *
         * You can use this endpoint to show different browser icons to your users.
         * The code argument receives the browser code as it appears in your user
         * /account/sessions endpoint. Use width, height and quality arguments to
         * change the output settings.
         *
         * @param {string} code
         * @param {number} width
         * @param {number} height
         * @param {number} quality
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getBrowser: (code: string, width?: number, height?: number, quality?: number): URL => {
            if (typeof code === 'undefined') {
                throw new AppwriteException('Missing required parameter: "code"');
            }

            let path = '/avatars/browsers/{code}'.replace('{code}', code);
            let payload: Payload = {};

            if (typeof width !== 'undefined') {
                payload['width'] = width;
            }

            if (typeof height !== 'undefined') {
                payload['height'] = height;
            }

            if (typeof quality !== 'undefined') {
                payload['quality'] = quality;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get Credit Card Icon
         *
         * The credit card endpoint will return you the icon of the credit card
         * provider you need. Use width, height and quality arguments to change the
         * output settings.
         *
         * @param {string} code
         * @param {number} width
         * @param {number} height
         * @param {number} quality
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getCreditCard: (code: string, width?: number, height?: number, quality?: number): URL => {
            if (typeof code === 'undefined') {
                throw new AppwriteException('Missing required parameter: "code"');
            }

            let path = '/avatars/credit-cards/{code}'.replace('{code}', code);
            let payload: Payload = {};

            if (typeof width !== 'undefined') {
                payload['width'] = width;
            }

            if (typeof height !== 'undefined') {
                payload['height'] = height;
            }

            if (typeof quality !== 'undefined') {
                payload['quality'] = quality;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get Favicon
         *
         * Use this endpoint to fetch the favorite icon (AKA favicon) of any remote
         * website URL.
         * 
         *
         * @param {string} url
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getFavicon: (url: string): URL => {
            if (typeof url === 'undefined') {
                throw new AppwriteException('Missing required parameter: "url"');
            }

            let path = '/avatars/favicon';
            let payload: Payload = {};

            if (typeof url !== 'undefined') {
                payload['url'] = url;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get Country Flag
         *
         * You can use this endpoint to show different country flags icons to your
         * users. The code argument receives the 2 letter country code. Use width,
         * height and quality arguments to change the output settings.
         *
         * @param {string} code
         * @param {number} width
         * @param {number} height
         * @param {number} quality
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getFlag: (code: string, width?: number, height?: number, quality?: number): URL => {
            if (typeof code === 'undefined') {
                throw new AppwriteException('Missing required parameter: "code"');
            }

            let path = '/avatars/flags/{code}'.replace('{code}', code);
            let payload: Payload = {};

            if (typeof width !== 'undefined') {
                payload['width'] = width;
            }

            if (typeof height !== 'undefined') {
                payload['height'] = height;
            }

            if (typeof quality !== 'undefined') {
                payload['quality'] = quality;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get Image from URL
         *
         * Use this endpoint to fetch a remote image URL and crop it to any image size
         * you want. This endpoint is very useful if you need to crop and display
         * remote images in your app or in case you want to make sure a 3rd party
         * image is properly served using a TLS protocol.
         *
         * @param {string} url
         * @param {number} width
         * @param {number} height
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getImage: (url: string, width?: number, height?: number): URL => {
            if (typeof url === 'undefined') {
                throw new AppwriteException('Missing required parameter: "url"');
            }

            let path = '/avatars/image';
            let payload: Payload = {};

            if (typeof url !== 'undefined') {
                payload['url'] = url;
            }

            if (typeof width !== 'undefined') {
                payload['width'] = width;
            }

            if (typeof height !== 'undefined') {
                payload['height'] = height;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get User Initials
         *
         * Use this endpoint to show your user initials avatar icon on your website or
         * app. By default, this route will try to print your logged-in user name or
         * email initials. You can also overwrite the user name if you pass the 'name'
         * parameter. If no name is given and no user is logged, an empty avatar will
         * be returned.
         * 
         * You can use the color and background params to change the avatar colors. By
         * default, a random theme will be selected. The random theme will persist for
         * the user's initials when reloading the same theme will always return for
         * the same initials.
         *
         * @param {string} name
         * @param {number} width
         * @param {number} height
         * @param {string} color
         * @param {string} background
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getInitials: (name?: string, width?: number, height?: number, color?: string, background?: string): URL => {
            let path = '/avatars/initials';
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            if (typeof width !== 'undefined') {
                payload['width'] = width;
            }

            if (typeof height !== 'undefined') {
                payload['height'] = height;
            }

            if (typeof color !== 'undefined') {
                payload['color'] = color;
            }

            if (typeof background !== 'undefined') {
                payload['background'] = background;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get QR Code
         *
         * Converts a given plain text to a QR code image. You can use the query
         * parameters to change the size and style of the resulting image.
         *
         * @param {string} text
         * @param {number} size
         * @param {number} margin
         * @param {boolean} download
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getQR: (text: string, size?: number, margin?: number, download?: boolean): URL => {
            if (typeof text === 'undefined') {
                throw new AppwriteException('Missing required parameter: "text"');
            }

            let path = '/avatars/qr';
            let payload: Payload = {};

            if (typeof text !== 'undefined') {
                payload['text'] = text;
            }

            if (typeof size !== 'undefined') {
                payload['size'] = size;
            }

            if (typeof margin !== 'undefined') {
                payload['margin'] = margin;
            }

            if (typeof download !== 'undefined') {
                payload['download'] = download;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        }
    };

    database = {

        /**
         * List Collections
         *
         * Get a list of all the user collections. You can use the query params to
         * filter your results. On admin mode, this endpoint will return a list of all
         * of the project's collections. [Learn more about different API
         * modes](/docs/admin).
         *
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        listCollections: async <T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            let path = '/database/collections';
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Collection
         *
         * Create a new Collection.
         *
         * @param {string} name
         * @param {string[]} read
         * @param {string[]} write
         * @param {string[]} rules
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createCollection: async <T extends unknown>(name: string, read: string[], write: string[], rules: string[]): Promise<T> => {
            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            if (typeof read === 'undefined') {
                throw new AppwriteException('Missing required parameter: "read"');
            }

            if (typeof write === 'undefined') {
                throw new AppwriteException('Missing required parameter: "write"');
            }

            if (typeof rules === 'undefined') {
                throw new AppwriteException('Missing required parameter: "rules"');
            }

            let path = '/database/collections';
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            if (typeof read !== 'undefined') {
                payload['read'] = read;
            }

            if (typeof write !== 'undefined') {
                payload['write'] = write;
            }

            if (typeof rules !== 'undefined') {
                payload['rules'] = rules;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Collection
         *
         * Get a collection by its unique ID. This endpoint response returns a JSON
         * object with the collection metadata.
         *
         * @param {string} collectionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getCollection: async <T extends unknown>(collectionId: string): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            let path = '/database/collections/{collectionId}'.replace('{collectionId}', collectionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Collection
         *
         * Update a collection by its unique ID.
         *
         * @param {string} collectionId
         * @param {string} name
         * @param {string[]} read
         * @param {string[]} write
         * @param {string[]} rules
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateCollection: async <T extends unknown>(collectionId: string, name: string, read?: string[], write?: string[], rules?: string[]): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            let path = '/database/collections/{collectionId}'.replace('{collectionId}', collectionId);
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            if (typeof read !== 'undefined') {
                payload['read'] = read;
            }

            if (typeof write !== 'undefined') {
                payload['write'] = write;
            }

            if (typeof rules !== 'undefined') {
                payload['rules'] = rules;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('put', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Collection
         *
         * Delete a collection by its unique ID. Only users with write permissions
         * have access to delete this resource.
         *
         * @param {string} collectionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteCollection: async <T extends unknown>(collectionId: string): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            let path = '/database/collections/{collectionId}'.replace('{collectionId}', collectionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Documents
         *
         * Get a list of all the user documents. You can use the query params to
         * filter your results. On admin mode, this endpoint will return a list of all
         * of the project's documents. [Learn more about different API
         * modes](/docs/admin).
         *
         * @param {string} collectionId
         * @param {string[]} filters
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderField
         * @param {string} orderType
         * @param {string} orderCast
         * @param {string} search
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        listDocuments: async <T extends unknown>(collectionId: string, filters?: string[], limit?: number, offset?: number, orderField?: string, orderType?: string, orderCast?: string, search?: string): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            let path = '/database/collections/{collectionId}/documents'.replace('{collectionId}', collectionId);
            let payload: Payload = {};

            if (typeof filters !== 'undefined') {
                payload['filters'] = filters;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderField !== 'undefined') {
                payload['orderField'] = orderField;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            if (typeof orderCast !== 'undefined') {
                payload['orderCast'] = orderCast;
            }

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Document
         *
         * Create a new Document. Before using this route, you should create a new
         * collection resource using either a [server
         * integration](/docs/server/database#databaseCreateCollection) API or
         * directly from your database console.
         *
         * @param {string} collectionId
         * @param {object} data
         * @param {string[]} read
         * @param {string[]} write
         * @param {string} parentDocument
         * @param {string} parentProperty
         * @param {string} parentPropertyType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createDocument: async <T extends unknown>(collectionId: string, data: object, read?: string[], write?: string[], parentDocument?: string, parentProperty?: string, parentPropertyType?: string): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            if (typeof data === 'undefined') {
                throw new AppwriteException('Missing required parameter: "data"');
            }

            let path = '/database/collections/{collectionId}/documents'.replace('{collectionId}', collectionId);
            let payload: Payload = {};

            if (typeof data !== 'undefined') {
                payload['data'] = data;
            }

            if (typeof read !== 'undefined') {
                payload['read'] = read;
            }

            if (typeof write !== 'undefined') {
                payload['write'] = write;
            }

            if (typeof parentDocument !== 'undefined') {
                payload['parentDocument'] = parentDocument;
            }

            if (typeof parentProperty !== 'undefined') {
                payload['parentProperty'] = parentProperty;
            }

            if (typeof parentPropertyType !== 'undefined') {
                payload['parentPropertyType'] = parentPropertyType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Document
         *
         * Get a document by its unique ID. This endpoint response returns a JSON
         * object with the document data.
         *
         * @param {string} collectionId
         * @param {string} documentId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getDocument: async <T extends unknown>(collectionId: string, documentId: string): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            if (typeof documentId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "documentId"');
            }

            let path = '/database/collections/{collectionId}/documents/{documentId}'.replace('{collectionId}', collectionId).replace('{documentId}', documentId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Document
         *
         * Update a document by its unique ID. Using the patch method you can pass
         * only specific fields that will get updated.
         *
         * @param {string} collectionId
         * @param {string} documentId
         * @param {object} data
         * @param {string[]} read
         * @param {string[]} write
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateDocument: async <T extends unknown>(collectionId: string, documentId: string, data: object, read?: string[], write?: string[]): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            if (typeof documentId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "documentId"');
            }

            if (typeof data === 'undefined') {
                throw new AppwriteException('Missing required parameter: "data"');
            }

            let path = '/database/collections/{collectionId}/documents/{documentId}'.replace('{collectionId}', collectionId).replace('{documentId}', documentId);
            let payload: Payload = {};

            if (typeof data !== 'undefined') {
                payload['data'] = data;
            }

            if (typeof read !== 'undefined') {
                payload['read'] = read;
            }

            if (typeof write !== 'undefined') {
                payload['write'] = write;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Document
         *
         * Delete a document by its unique ID. This endpoint deletes only the parent
         * documents, its attributes and relations to other documents. Child documents
         * **will not** be deleted.
         *
         * @param {string} collectionId
         * @param {string} documentId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteDocument: async <T extends unknown>(collectionId: string, documentId: string): Promise<T> => {
            if (typeof collectionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "collectionId"');
            }

            if (typeof documentId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "documentId"');
            }

            let path = '/database/collections/{collectionId}/documents/{documentId}'.replace('{collectionId}', collectionId).replace('{documentId}', documentId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

    functions = {

        /**
         * List Functions
         *
         * Get a list of all the project's functions. You can use the query params to
         * filter your results.
         *
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        list: async <T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            let path = '/functions';
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Function
         *
         * Create a new function. You can pass a list of
         * [permissions](/docs/permissions) to allow different project users or team
         * with access to execute the function using the client API.
         *
         * @param {string} name
         * @param {string[]} execute
         * @param {string} runtime
         * @param {object} vars
         * @param {string[]} events
         * @param {string} schedule
         * @param {number} timeout
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        create: async <T extends unknown>(name: string, execute: string[], runtime: string, vars?: object, events?: string[], schedule?: string, timeout?: number): Promise<T> => {
            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            if (typeof execute === 'undefined') {
                throw new AppwriteException('Missing required parameter: "execute"');
            }

            if (typeof runtime === 'undefined') {
                throw new AppwriteException('Missing required parameter: "runtime"');
            }

            let path = '/functions';
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            if (typeof execute !== 'undefined') {
                payload['execute'] = execute;
            }

            if (typeof runtime !== 'undefined') {
                payload['runtime'] = runtime;
            }

            if (typeof vars !== 'undefined') {
                payload['vars'] = vars;
            }

            if (typeof events !== 'undefined') {
                payload['events'] = events;
            }

            if (typeof schedule !== 'undefined') {
                payload['schedule'] = schedule;
            }

            if (typeof timeout !== 'undefined') {
                payload['timeout'] = timeout;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Function
         *
         * Get a function by its unique ID.
         *
         * @param {string} functionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        get: async <T extends unknown>(functionId: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            let path = '/functions/{functionId}'.replace('{functionId}', functionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Function
         *
         * Update function by its unique ID.
         *
         * @param {string} functionId
         * @param {string} name
         * @param {string[]} execute
         * @param {object} vars
         * @param {string[]} events
         * @param {string} schedule
         * @param {number} timeout
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        update: async <T extends unknown>(functionId: string, name: string, execute: string[], vars?: object, events?: string[], schedule?: string, timeout?: number): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            if (typeof execute === 'undefined') {
                throw new AppwriteException('Missing required parameter: "execute"');
            }

            let path = '/functions/{functionId}'.replace('{functionId}', functionId);
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            if (typeof execute !== 'undefined') {
                payload['execute'] = execute;
            }

            if (typeof vars !== 'undefined') {
                payload['vars'] = vars;
            }

            if (typeof events !== 'undefined') {
                payload['events'] = events;
            }

            if (typeof schedule !== 'undefined') {
                payload['schedule'] = schedule;
            }

            if (typeof timeout !== 'undefined') {
                payload['timeout'] = timeout;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('put', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Function
         *
         * Delete a function by its unique ID.
         *
         * @param {string} functionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        delete: async <T extends unknown>(functionId: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            let path = '/functions/{functionId}'.replace('{functionId}', functionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Executions
         *
         * Get a list of all the current user function execution logs. You can use the
         * query params to filter your results. On admin mode, this endpoint will
         * return a list of all of the project's executions. [Learn more about
         * different API modes](/docs/admin).
         *
         * @param {string} functionId
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        listExecutions: async <T extends unknown>(functionId: string, search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            let path = '/functions/{functionId}/executions'.replace('{functionId}', functionId);
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Execution
         *
         * Trigger a function execution. The returned object will return you the
         * current execution status. You can ping the `Get Execution` endpoint to get
         * updates on the current execution status. Once this endpoint is called, your
         * function execution process will start asynchronously.
         *
         * @param {string} functionId
         * @param {string} data
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createExecution: async <T extends unknown>(functionId: string, data?: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            let path = '/functions/{functionId}/executions'.replace('{functionId}', functionId);
            let payload: Payload = {};

            if (typeof data !== 'undefined') {
                payload['data'] = data;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Execution
         *
         * Get a function execution log by its unique ID.
         *
         * @param {string} functionId
         * @param {string} executionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getExecution: async <T extends unknown>(functionId: string, executionId: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            if (typeof executionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "executionId"');
            }

            let path = '/functions/{functionId}/executions/{executionId}'.replace('{functionId}', functionId).replace('{executionId}', executionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Function Tag
         *
         * Update the function code tag ID using the unique function ID. Use this
         * endpoint to switch the code tag that should be executed by the execution
         * endpoint.
         *
         * @param {string} functionId
         * @param {string} tag
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateTag: async <T extends unknown>(functionId: string, tag: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            if (typeof tag === 'undefined') {
                throw new AppwriteException('Missing required parameter: "tag"');
            }

            let path = '/functions/{functionId}/tag'.replace('{functionId}', functionId);
            let payload: Payload = {};

            if (typeof tag !== 'undefined') {
                payload['tag'] = tag;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Tags
         *
         * Get a list of all the project's code tags. You can use the query params to
         * filter your results.
         *
         * @param {string} functionId
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        listTags: async <T extends unknown>(functionId: string, search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            let path = '/functions/{functionId}/tags'.replace('{functionId}', functionId);
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Tag
         *
         * Create a new function code tag. Use this endpoint to upload a new version
         * of your code function. To execute your newly uploaded code, you'll need to
         * update the function's tag to use your new tag UID.
         * 
         * This endpoint accepts a tar.gz file compressed with your code. Make sure to
         * include any dependencies your code has within the compressed file. You can
         * learn more about code packaging in the [Appwrite Cloud Functions
         * tutorial](/docs/functions).
         * 
         * Use the "command" param to set the entry point used to execute your code.
         *
         * @param {string} functionId
         * @param {string} command
         * @param {File} code
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createTag: async <T extends unknown>(functionId: string, command: string, code: File): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            if (typeof command === 'undefined') {
                throw new AppwriteException('Missing required parameter: "command"');
            }

            if (typeof code === 'undefined') {
                throw new AppwriteException('Missing required parameter: "code"');
            }

            let path = '/functions/{functionId}/tags'.replace('{functionId}', functionId);
            let payload: Payload = {};

            if (typeof command !== 'undefined') {
                payload['command'] = command;
            }

            if (typeof code !== 'undefined') {
                payload['code'] = code;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'multipart/form-data',
            }, payload);
        },

        /**
         * Get Tag
         *
         * Get a code tag by its unique ID.
         *
         * @param {string} functionId
         * @param {string} tagId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getTag: async <T extends unknown>(functionId: string, tagId: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            if (typeof tagId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "tagId"');
            }

            let path = '/functions/{functionId}/tags/{tagId}'.replace('{functionId}', functionId).replace('{tagId}', tagId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Tag
         *
         * Delete a code tag by its unique ID.
         *
         * @param {string} functionId
         * @param {string} tagId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteTag: async <T extends unknown>(functionId: string, tagId: string): Promise<T> => {
            if (typeof functionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "functionId"');
            }

            if (typeof tagId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "tagId"');
            }

            let path = '/functions/{functionId}/tags/{tagId}'.replace('{functionId}', functionId).replace('{tagId}', tagId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

    health = {

        /**
         * Get HTTP
         *
         * Check the Appwrite HTTP server is up and responsive.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        get: async <T extends unknown>(): Promise<T> => {
            let path = '/health';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Anti virus
         *
         * Check the Appwrite Anti Virus server is up and connection is successful.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getAntiVirus: async <T extends unknown>(): Promise<T> => {
            let path = '/health/anti-virus';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Cache
         *
         * Check the Appwrite in-memory cache server is up and connection is
         * successful.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getCache: async <T extends unknown>(): Promise<T> => {
            let path = '/health/cache';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get DB
         *
         * Check the Appwrite database server is up and connection is successful.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getDB: async <T extends unknown>(): Promise<T> => {
            let path = '/health/db';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Certificates Queue
         *
         * Get the number of certificates that are waiting to be issued against
         * [Letsencrypt](https://letsencrypt.org/) in the Appwrite internal queue
         * server.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getQueueCertificates: async <T extends unknown>(): Promise<T> => {
            let path = '/health/queue/certificates';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Functions Queue
         *
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getQueueFunctions: async <T extends unknown>(): Promise<T> => {
            let path = '/health/queue/functions';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Logs Queue
         *
         * Get the number of logs that are waiting to be processed in the Appwrite
         * internal queue server.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getQueueLogs: async <T extends unknown>(): Promise<T> => {
            let path = '/health/queue/logs';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Tasks Queue
         *
         * Get the number of tasks that are waiting to be processed in the Appwrite
         * internal queue server.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getQueueTasks: async <T extends unknown>(): Promise<T> => {
            let path = '/health/queue/tasks';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Usage Queue
         *
         * Get the number of usage stats that are waiting to be processed in the
         * Appwrite internal queue server.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getQueueUsage: async <T extends unknown>(): Promise<T> => {
            let path = '/health/queue/usage';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Webhooks Queue
         *
         * Get the number of webhooks that are waiting to be processed in the Appwrite
         * internal queue server.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getQueueWebhooks: async <T extends unknown>(): Promise<T> => {
            let path = '/health/queue/webhooks';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Local Storage
         *
         * Check the Appwrite local storage device is up and connection is successful.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getStorageLocal: async <T extends unknown>(): Promise<T> => {
            let path = '/health/storage/local';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Time
         *
         * Check the Appwrite server time is synced with Google remote NTP server. We
         * use this technology to smoothly handle leap seconds with no disruptive
         * events. The [Network Time
         * Protocol](https://en.wikipedia.org/wiki/Network_Time_Protocol) (NTP) is
         * used by hundreds of millions of computers and devices to synchronize their
         * clocks over the Internet. If your computer sets its own clock, it likely
         * uses NTP.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getTime: async <T extends unknown>(): Promise<T> => {
            let path = '/health/time';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

    locale = {

        /**
         * Get User Locale
         *
         * Get the current user location based on IP. Returns an object with user
         * country code, country name, continent name, continent code, ip address and
         * suggested currency. You can use the locale header to get the data in a
         * supported language.
         * 
         * ([IP Geolocation by DB-IP](https://db-ip.com))
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        get: async <T extends unknown>(): Promise<T> => {
            let path = '/locale';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Continents
         *
         * List of all continents. You can use the locale header to get the data in a
         * supported language.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getContinents: async <T extends unknown>(): Promise<T> => {
            let path = '/locale/continents';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Countries
         *
         * List of all countries. You can use the locale header to get the data in a
         * supported language.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getCountries: async <T extends unknown>(): Promise<T> => {
            let path = '/locale/countries';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List EU Countries
         *
         * List of all countries that are currently members of the EU. You can use the
         * locale header to get the data in a supported language.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getCountriesEU: async <T extends unknown>(): Promise<T> => {
            let path = '/locale/countries/eu';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Countries Phone Codes
         *
         * List of all countries phone codes. You can use the locale header to get the
         * data in a supported language.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getCountriesPhones: async <T extends unknown>(): Promise<T> => {
            let path = '/locale/countries/phones';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Currencies
         *
         * List of all currencies, including currency symbol, name, plural, and
         * decimal digits for all major and minor currencies. You can use the locale
         * header to get the data in a supported language.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getCurrencies: async <T extends unknown>(): Promise<T> => {
            let path = '/locale/currencies';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * List Languages
         *
         * List of all languages classified by ISO 639-1 including 2-letter code, name
         * in English, and name in the respective language.
         *
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getLanguages: async <T extends unknown>(): Promise<T> => {
            let path = '/locale/languages';
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

    storage = {

        /**
         * List Files
         *
         * Get a list of all the user files. You can use the query params to filter
         * your results. On admin mode, this endpoint will return a list of all of the
         * project's files. [Learn more about different API modes](/docs/admin).
         *
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        listFiles: async <T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            let path = '/storage/files';
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create File
         *
         * Create a new file. The user who creates the file will automatically be
         * assigned to read and write access unless he has passed custom values for
         * read and write arguments.
         *
         * @param {File} file
         * @param {string[]} read
         * @param {string[]} write
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createFile: async <T extends unknown>(file: File, read?: string[], write?: string[]): Promise<T> => {
            if (typeof file === 'undefined') {
                throw new AppwriteException('Missing required parameter: "file"');
            }

            let path = '/storage/files';
            let payload: Payload = {};

            if (typeof file !== 'undefined') {
                payload['file'] = file;
            }

            if (typeof read !== 'undefined') {
                payload['read'] = read;
            }

            if (typeof write !== 'undefined') {
                payload['write'] = write;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'multipart/form-data',
            }, payload);
        },

        /**
         * Get File
         *
         * Get a file by its unique ID. This endpoint response returns a JSON object
         * with the file metadata.
         *
         * @param {string} fileId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getFile: async <T extends unknown>(fileId: string): Promise<T> => {
            if (typeof fileId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "fileId"');
            }

            let path = '/storage/files/{fileId}'.replace('{fileId}', fileId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update File
         *
         * Update a file by its unique ID. Only users with write permissions have
         * access to update this resource.
         *
         * @param {string} fileId
         * @param {string[]} read
         * @param {string[]} write
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateFile: async <T extends unknown>(fileId: string, read: string[], write: string[]): Promise<T> => {
            if (typeof fileId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "fileId"');
            }

            if (typeof read === 'undefined') {
                throw new AppwriteException('Missing required parameter: "read"');
            }

            if (typeof write === 'undefined') {
                throw new AppwriteException('Missing required parameter: "write"');
            }

            let path = '/storage/files/{fileId}'.replace('{fileId}', fileId);
            let payload: Payload = {};

            if (typeof read !== 'undefined') {
                payload['read'] = read;
            }

            if (typeof write !== 'undefined') {
                payload['write'] = write;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('put', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete File
         *
         * Delete a file by its unique ID. Only users with write permissions have
         * access to delete this resource.
         *
         * @param {string} fileId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteFile: async <T extends unknown>(fileId: string): Promise<T> => {
            if (typeof fileId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "fileId"');
            }

            let path = '/storage/files/{fileId}'.replace('{fileId}', fileId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get File for Download
         *
         * Get a file content by its unique ID. The endpoint response return with a
         * 'Content-Disposition: attachment' header that tells the browser to start
         * downloading the file to user downloads directory.
         *
         * @param {string} fileId
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getFileDownload: (fileId: string): URL => {
            if (typeof fileId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "fileId"');
            }

            let path = '/storage/files/{fileId}/download'.replace('{fileId}', fileId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get File Preview
         *
         * Get a file preview image. Currently, this method supports preview for image
         * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
         * and spreadsheets, will return the file icon image. You can also pass query
         * string arguments for cutting and resizing your preview image.
         *
         * @param {string} fileId
         * @param {number} width
         * @param {number} height
         * @param {string} gravity
         * @param {number} quality
         * @param {number} borderWidth
         * @param {string} borderColor
         * @param {number} borderRadius
         * @param {number} opacity
         * @param {number} rotation
         * @param {string} background
         * @param {string} output
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getFilePreview: (fileId: string, width?: number, height?: number, gravity?: string, quality?: number, borderWidth?: number, borderColor?: string, borderRadius?: number, opacity?: number, rotation?: number, background?: string, output?: string): URL => {
            if (typeof fileId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "fileId"');
            }

            let path = '/storage/files/{fileId}/preview'.replace('{fileId}', fileId);
            let payload: Payload = {};

            if (typeof width !== 'undefined') {
                payload['width'] = width;
            }

            if (typeof height !== 'undefined') {
                payload['height'] = height;
            }

            if (typeof gravity !== 'undefined') {
                payload['gravity'] = gravity;
            }

            if (typeof quality !== 'undefined') {
                payload['quality'] = quality;
            }

            if (typeof borderWidth !== 'undefined') {
                payload['borderWidth'] = borderWidth;
            }

            if (typeof borderColor !== 'undefined') {
                payload['borderColor'] = borderColor;
            }

            if (typeof borderRadius !== 'undefined') {
                payload['borderRadius'] = borderRadius;
            }

            if (typeof opacity !== 'undefined') {
                payload['opacity'] = opacity;
            }

            if (typeof rotation !== 'undefined') {
                payload['rotation'] = rotation;
            }

            if (typeof background !== 'undefined') {
                payload['background'] = background;
            }

            if (typeof output !== 'undefined') {
                payload['output'] = output;
            }

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        },

        /**
         * Get File for View
         *
         * Get a file content by its unique ID. This endpoint is similar to the
         * download method but returns with no  'Content-Disposition: attachment'
         * header.
         *
         * @param {string} fileId
         * @throws {AppwriteException}
         * @returns {URL}
         */
        getFileView: (fileId: string): URL => {
            if (typeof fileId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "fileId"');
            }

            let path = '/storage/files/{fileId}/view'.replace('{fileId}', fileId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            payload['project'] = this.config.project;

            payload['key'] = this.config.key;


            for (const [key, value] of Object.entries(this.flatten(payload))) {
                uri.searchParams.append(key, value);
            }
            return uri;
        }
    };

    teams = {

        /**
         * List Teams
         *
         * Get a list of all the current user teams. You can use the query params to
         * filter your results. On admin mode, this endpoint will return a list of all
         * of the project's teams. [Learn more about different API
         * modes](/docs/admin).
         *
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        list: async <T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            let path = '/teams';
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Team
         *
         * Create a new team. The user who creates the team will automatically be
         * assigned as the owner of the team. The team owner can invite new members,
         * who will be able add new owners and update or delete the team from your
         * project.
         *
         * @param {string} name
         * @param {string[]} roles
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        create: async <T extends unknown>(name: string, roles?: string[]): Promise<T> => {
            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            let path = '/teams';
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            if (typeof roles !== 'undefined') {
                payload['roles'] = roles;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Team
         *
         * Get a team by its unique ID. All team members have read access for this
         * resource.
         *
         * @param {string} teamId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        get: async <T extends unknown>(teamId: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            let path = '/teams/{teamId}'.replace('{teamId}', teamId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Team
         *
         * Update a team by its unique ID. Only team owners have write access for this
         * resource.
         *
         * @param {string} teamId
         * @param {string} name
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        update: async <T extends unknown>(teamId: string, name: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            let path = '/teams/{teamId}'.replace('{teamId}', teamId);
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('put', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Team
         *
         * Delete a team by its unique ID. Only team owners have write access for this
         * resource.
         *
         * @param {string} teamId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        delete: async <T extends unknown>(teamId: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            let path = '/teams/{teamId}'.replace('{teamId}', teamId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get Team Memberships
         *
         * Get a team members by the team unique ID. All team members have read access
         * for this list of resources.
         *
         * @param {string} teamId
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getMemberships: async <T extends unknown>(teamId: string, search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            let path = '/teams/{teamId}/memberships'.replace('{teamId}', teamId);
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create Team Membership
         *
         * Use this endpoint to invite a new member to join your team. If initiated
         * from Client SDK, an email with a link to join the team will be sent to the
         * new member's email address if the member doesn't exist in the project it
         * will be created automatically. If initiated from server side SDKs, new
         * member will automatically be added to the team.
         * 
         * Use the 'URL' parameter to redirect the user from the invitation email back
         * to your app. When the user is redirected, use the [Update Team Membership
         * Status](/docs/client/teams#teamsUpdateMembershipStatus) endpoint to allow
         * the user to accept the invitation to the team.  While calling from side
         * SDKs the redirect url can be empty string.
         * 
         * Please note that in order to avoid a [Redirect
         * Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
         * the only valid redirect URL's are the once from domains you have set when
         * added your platforms in the console interface.
         *
         * @param {string} teamId
         * @param {string} email
         * @param {string[]} roles
         * @param {string} url
         * @param {string} name
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        createMembership: async <T extends unknown>(teamId: string, email: string, roles: string[], url: string, name?: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            if (typeof email === 'undefined') {
                throw new AppwriteException('Missing required parameter: "email"');
            }

            if (typeof roles === 'undefined') {
                throw new AppwriteException('Missing required parameter: "roles"');
            }

            if (typeof url === 'undefined') {
                throw new AppwriteException('Missing required parameter: "url"');
            }

            let path = '/teams/{teamId}/memberships'.replace('{teamId}', teamId);
            let payload: Payload = {};

            if (typeof email !== 'undefined') {
                payload['email'] = email;
            }

            if (typeof roles !== 'undefined') {
                payload['roles'] = roles;
            }

            if (typeof url !== 'undefined') {
                payload['url'] = url;
            }

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Membership Roles
         *
         *
         * @param {string} teamId
         * @param {string} membershipId
         * @param {string[]} roles
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateMembershipRoles: async <T extends unknown>(teamId: string, membershipId: string, roles: string[]): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            if (typeof membershipId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "membershipId"');
            }

            if (typeof roles === 'undefined') {
                throw new AppwriteException('Missing required parameter: "roles"');
            }

            let path = '/teams/{teamId}/memberships/{membershipId}'.replace('{teamId}', teamId).replace('{membershipId}', membershipId);
            let payload: Payload = {};

            if (typeof roles !== 'undefined') {
                payload['roles'] = roles;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete Team Membership
         *
         * This endpoint allows a user to leave a team or for a team owner to delete
         * the membership of any other team member. You can also use this endpoint to
         * delete a user membership even if it is not accepted.
         *
         * @param {string} teamId
         * @param {string} membershipId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteMembership: async <T extends unknown>(teamId: string, membershipId: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            if (typeof membershipId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "membershipId"');
            }

            let path = '/teams/{teamId}/memberships/{membershipId}'.replace('{teamId}', teamId).replace('{membershipId}', membershipId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Team Membership Status
         *
         * Use this endpoint to allow a user to accept an invitation to join a team
         * after being redirected back to your app from the invitation email recieved
         * by the user.
         *
         * @param {string} teamId
         * @param {string} membershipId
         * @param {string} userId
         * @param {string} secret
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateMembershipStatus: async <T extends unknown>(teamId: string, membershipId: string, userId: string, secret: string): Promise<T> => {
            if (typeof teamId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "teamId"');
            }

            if (typeof membershipId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "membershipId"');
            }

            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof secret === 'undefined') {
                throw new AppwriteException('Missing required parameter: "secret"');
            }

            let path = '/teams/{teamId}/memberships/{membershipId}/status'.replace('{teamId}', teamId).replace('{membershipId}', membershipId);
            let payload: Payload = {};

            if (typeof userId !== 'undefined') {
                payload['userId'] = userId;
            }

            if (typeof secret !== 'undefined') {
                payload['secret'] = secret;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

    users = {

        /**
         * List Users
         *
         * Get a list of all the project's users. You can use the query params to
         * filter your results.
         *
         * @param {string} search
         * @param {number} limit
         * @param {number} offset
         * @param {string} orderType
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        list: async <T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> => {
            let path = '/users';
            let payload: Payload = {};

            if (typeof search !== 'undefined') {
                payload['search'] = search;
            }

            if (typeof limit !== 'undefined') {
                payload['limit'] = limit;
            }

            if (typeof offset !== 'undefined') {
                payload['offset'] = offset;
            }

            if (typeof orderType !== 'undefined') {
                payload['orderType'] = orderType;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Create User
         *
         * Create a new user.
         *
         * @param {string} email
         * @param {string} password
         * @param {string} name
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        create: async <T extends unknown>(email: string, password: string, name?: string): Promise<T> => {
            if (typeof email === 'undefined') {
                throw new AppwriteException('Missing required parameter: "email"');
            }

            if (typeof password === 'undefined') {
                throw new AppwriteException('Missing required parameter: "password"');
            }

            let path = '/users';
            let payload: Payload = {};

            if (typeof email !== 'undefined') {
                payload['email'] = email;
            }

            if (typeof password !== 'undefined') {
                payload['password'] = password;
            }

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('post', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get User
         *
         * Get a user by its unique ID.
         *
         * @param {string} userId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        get: async <T extends unknown>(userId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            let path = '/users/{userId}'.replace('{userId}', userId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete User
         *
         * Delete a user by its unique ID.
         *
         * @param {string} userId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        delete: async <T extends unknown>(userId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            let path = '/users/{userId}'.replace('{userId}', userId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Email
         *
         * Update the user email by its unique ID.
         *
         * @param {string} userId
         * @param {string} email
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateEmail: async <T extends unknown>(userId: string, email: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof email === 'undefined') {
                throw new AppwriteException('Missing required parameter: "email"');
            }

            let path = '/users/{userId}/email'.replace('{userId}', userId);
            let payload: Payload = {};

            if (typeof email !== 'undefined') {
                payload['email'] = email;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get User Logs
         *
         * Get a user activity logs list by its unique ID.
         *
         * @param {string} userId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getLogs: async <T extends unknown>(userId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            let path = '/users/{userId}/logs'.replace('{userId}', userId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Name
         *
         * Update the user name by its unique ID.
         *
         * @param {string} userId
         * @param {string} name
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateName: async <T extends unknown>(userId: string, name: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof name === 'undefined') {
                throw new AppwriteException('Missing required parameter: "name"');
            }

            let path = '/users/{userId}/name'.replace('{userId}', userId);
            let payload: Payload = {};

            if (typeof name !== 'undefined') {
                payload['name'] = name;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Password
         *
         * Update the user password by its unique ID.
         *
         * @param {string} userId
         * @param {string} password
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updatePassword: async <T extends unknown>(userId: string, password: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof password === 'undefined') {
                throw new AppwriteException('Missing required parameter: "password"');
            }

            let path = '/users/{userId}/password'.replace('{userId}', userId);
            let payload: Payload = {};

            if (typeof password !== 'undefined') {
                payload['password'] = password;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get User Preferences
         *
         * Get the user preferences by its unique ID.
         *
         * @param {string} userId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getPrefs: async <T extends unknown>(userId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            let path = '/users/{userId}/prefs'.replace('{userId}', userId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update User Preferences
         *
         * Update the user preferences by its unique ID. You can pass only the
         * specific settings you wish to update.
         *
         * @param {string} userId
         * @param {object} prefs
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updatePrefs: async <T extends unknown>(userId: string, prefs: object): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof prefs === 'undefined') {
                throw new AppwriteException('Missing required parameter: "prefs"');
            }

            let path = '/users/{userId}/prefs'.replace('{userId}', userId);
            let payload: Payload = {};

            if (typeof prefs !== 'undefined') {
                payload['prefs'] = prefs;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Get User Sessions
         *
         * Get the user sessions list by its unique ID.
         *
         * @param {string} userId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        getSessions: async <T extends unknown>(userId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            let path = '/users/{userId}/sessions'.replace('{userId}', userId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('get', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete User Sessions
         *
         * Delete all user's sessions by using the user's unique ID.
         *
         * @param {string} userId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteSessions: async <T extends unknown>(userId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            let path = '/users/{userId}/sessions'.replace('{userId}', userId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Delete User Session
         *
         * Delete a user sessions by its unique ID.
         *
         * @param {string} userId
         * @param {string} sessionId
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        deleteSession: async <T extends unknown>(userId: string, sessionId: string): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof sessionId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "sessionId"');
            }

            let path = '/users/{userId}/sessions/{sessionId}'.replace('{userId}', userId).replace('{sessionId}', sessionId);
            let payload: Payload = {};

            const uri = new URL(this.config.endpoint + path);
            return await this.call('delete', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update User Status
         *
         * Update the user status by its unique ID.
         *
         * @param {string} userId
         * @param {number} status
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateStatus: async <T extends unknown>(userId: string, status: number): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof status === 'undefined') {
                throw new AppwriteException('Missing required parameter: "status"');
            }

            let path = '/users/{userId}/status'.replace('{userId}', userId);
            let payload: Payload = {};

            if (typeof status !== 'undefined') {
                payload['status'] = status;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        },

        /**
         * Update Email Verification
         *
         * Update the user email verification status by its unique ID.
         *
         * @param {string} userId
         * @param {boolean} emailVerification
         * @throws {AppwriteException}
         * @returns {Promise}
         */
        updateVerification: async <T extends unknown>(userId: string, emailVerification: boolean): Promise<T> => {
            if (typeof userId === 'undefined') {
                throw new AppwriteException('Missing required parameter: "userId"');
            }

            if (typeof emailVerification === 'undefined') {
                throw new AppwriteException('Missing required parameter: "emailVerification"');
            }

            let path = '/users/{userId}/verification'.replace('{userId}', userId);
            let payload: Payload = {};

            if (typeof emailVerification !== 'undefined') {
                payload['emailVerification'] = emailVerification;
            }

            const uri = new URL(this.config.endpoint + path);
            return await this.call('patch', uri, {
                'content-type': 'application/json',
            }, payload);
        }
    };

};

export { Appwrite }
export type { AppwriteException }
