import 'package:dio/dio.dart';

class Client {
    String endPoint;
    Map<String, String> headers;
    bool selfSigned;
    Dio http;
    
    Client() {
        this.endPoint = 'https://appwrite.io/v1';
        this.headers = {
            'content-type': '',
            'x-sdk-version': 'appwrite:dart:0.0.1',
        };
        this.selfSigned = false;

        this.http = Dio();
        http.options.baseUrl = this.endPoint;
    }


     /// Your Appwrite project ID. You can find your project ID in your Appwrite console project settings.
    Client setProject(value) {
        this.addHeader('X-Appwrite-Project', value);

        return this;
    }


     /// Your Appwrite project secret key. You can can create a new API key from your Appwrite console API keys dashboard.
    Client setKey(value) {
        this.addHeader('X-Appwrite-Key', value);

        return this;
    }


    Client setLocale(value) {
        this.addHeader('X-Appwrite-Locale', value);

        return this;
    }


    Client setMode(value) {
        this.addHeader('X-Appwrite-Mode', value);

        return this;
    }

    Client setSelfSigned({bool status = true}) {
        this.selfSigned = status;

        return this;
    }

    Client setEndpoint(String endPoint)
    {
        this.endPoint = endPoint;

        return this;
    }

    Client addHeader(String key, String value) {
        this.headers[key.toLowerCase()] = value.toLowerCase();
        
        return this;
    }
      
    call(String method, {String path = '', Map<String, String> headers = const {}, Map<String, String> params = const {}}) {
        if(this.selfSigned) { 
            // Allow self signed requests
        }

        Options options = Options(
            headers: {...this.headers, ...headers},
        );

        if (method.toUpperCase() == "GET") {
            return http.post(path, data: params, options: options);
        }

        return http.get(path, options: options);
    }
}