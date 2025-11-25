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
        guard let cookies = UserDefaults.standard.stringArray(forKey: domain) else {
            return
        }
        for cookie in cookies {
            add(name: "Cookie", value: cookie)
        }
    }
}