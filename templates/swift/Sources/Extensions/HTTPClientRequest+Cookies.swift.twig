import AsyncHTTPClient
import Foundation
import NIO
import NIOHTTP1

extension HTTPClient.Request {
    public mutating func addDomainCookies() {
        headers.addDomainCookies(for: url.host!)
    }
}

extension HTTPClientRequest {
    public mutating func addDomainCookies() {
        headers.addDomainCookies(for: URL(string: url)!.host!)
    }
}

extension HTTPHeaders {
    public mutating func addDomainCookies(for domain: String) {
        let cookieJson = UserDefaults.standard.string(forKey: "\(domain)-cookies")
        let cookies: [HTTPClient.Cookie?]? = try? cookieJson?.fromJson(to: [HTTPClient.Cookie].self)
            ?? [(try? cookieJson?.fromJson(to: HTTPClient.Cookie.self))]

        if let authCookie = cookies?.first(where: { $0?.name.starts(with: "a_session_") == true } ) {
            add(name: "cookie", value: "\(authCookie!.name)=\(authCookie!.value)")
        }
    }
}