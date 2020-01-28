//
// Client.swift
//
// Created by Armino <devel@boioiong.com>
// GitHub: https://github.com/armino-dev/sdk-generator
//

import Foundation

open class Client {

    // MARK: Properties

    open var selfSigned = false

    open var endpoint = "https://appwrite.io/v1"

    open var headers: [String: String] = [
      "content-type": "",
      "x-sdk-version": "appwrite:swift:0.0.1"
    ]


    // MARK: Methods

    // default constructor
    public init() {

    }

        ///
    /// Set Project
    ///
        /// Your Appwrite project ID
    ///
        /// @param String value
    ///
    /// @return Client
    ///
    open func setProject(value: String) -> Client {

        self.addHeader(key: "X-Appwrite-Project", value: value)
        return self
    }

        ///
    /// Set Key
    ///
        /// Your Appwrite project secret key
    ///
        /// @param String value
    ///
    /// @return Client
    ///
    open func setKey(value: String) -> Client {

        self.addHeader(key: "X-Appwrite-Key", value: value)
        return self
    }

        ///
    /// Set Locale
    ///
        /// @param String value
    ///
    /// @return Client
    ///
    open func setLocale(value: String) -> Client {

        self.addHeader(key: "X-Appwrite-Locale", value: value)
        return self
    }

        ///
    /// Set Mode
    ///
        /// @param String value
    ///
    /// @return Client
    ///
    open func setMode(value: String) -> Client {

        self.addHeader(key: "X-Appwrite-Mode", value: value)
        return self
    }

    
    ///
    /// @param Bool status
    /// @return Client
    ///
    open func setSelfSigned(status: Bool = true) -> Client {

        self.selfSigned = status
        return self
    }

    ///
    /// @param String endpoint
    /// @return Client
    ///
    open func setEndpoint(endpoint: String) -> Client {

        self.endpoint = endpoint
        return self
    }

    ///
    /// @param String key
    /// @param String value
    ///
    open func addHeader(key: String, value: String) -> Client {

        self.headers[key.lowercased()] = value.lowercased()

        return self
    }

    open func flatten(params: [String: Any]) -> String {
        return ""
    }

    open func httpBuildQuery(params: [String: Any]) -> String {
        return ""
    }

    ///
    /// Make an API call
    ///
    /// @param String method
    /// @param String path
    /// @param Array params
    /// @param Array headers
    /// @return Array|String
    /// @throws Exception
    ///
    func call(method:String, path:String = "", headers:[String: String] = [:], params:[String: Any] = [:]) -> Any {

        //let headers = self.headers + headers
        self.headers.merge(headers){(current, _) in current}
        let targetURL:URL = URL(string: self.endpoint + path + (( method == HTTPMethod.get.rawValue && !params.isEmpty ) ? "?" + httpBuildQuery(params: params) : ""))!

        var query: String = ""

        var responseHeaders: Array<Any>  = []
        var responseStatus: Int = HTTPStatus.unknown.rawValue
        var responseType: String = ""
        var responseBody: String = ""

        switch (self.headers["content-type"]) {
            case "application/json":
              do {
                let json = try JSONSerialization.data(withJSONObject:params, options: [])
                query = String( data: json, encoding: String.Encoding.utf8)!
              } catch let error as NSError {
                print("Failed to parse json: \(error.localizedDescription)");
              }
              break
            case "multipart/form-data":
              query = self.flatten(params: params)
              break
            default:
              query = httpBuildQuery(params: params)
              break
        }

        var request = URLRequest(url: targetURL)
        let session = URLSession.shared

        request.httpMethod = method
        request.httpBody = query.data(using: .utf8)

        let task = session.dataTask(with: request, completionHandler: { data, response, error in
            if (error != nil) {
                print(error)
                return
            }

            //more to go here
        })
        task.resume()

        return responseBody
    }

}

extension Client {

    public enum HTTPStatus: Int {
      case unknown = -1

      case ok = 200
      case created = 201
      case accepted = 202

      case movedPermanently = 301
      case found = 302

      case badRequest = 400
      case notAuthorized = 401
      case paymentRequired = 402
      case forbidden = 403
      case notFound = 404
      case methodNotAllowed = 405
      case notAcceptable = 406

      case internalServerError = 500
      case notImplemented = 501
    }

    public enum HTTPMethod: String {
      case get

      case post
      case put
      case patch

      case delete

      case head
      case options
      case connect
      case trace
    }


}
