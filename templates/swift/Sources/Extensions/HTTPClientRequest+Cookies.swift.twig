import AsyncHTTPClient
import Foundation

extension HTTPClient.Request {
    public mutating func addDomainCookies() {
        let cookieJson = UserDefaults.standard.string(forKey: "\(url.host!)-cookies")
        let cookies: [HTTPClient.Cookie?]? = try? cookieJson?.fromJson(to: [HTTPClient.Cookie].self)
            ?? [(try? cookieJson?.fromJson(to: HTTPClient.Cookie.self))]

        if let authCookie = cookies?.first(where: { $0?.name.starts(with: "a_session_") == true } ) {
            headers.add(name: "cookie", value: "\(authCookie!.name)=\(authCookie!.value)")
        }
    }
}