import Foundation
import AsyncHTTPClient

extension HTTPClient.Cookie : Codable {

    enum CodingKeys: String, CodingKey {
        case name
        case value
        case path
        case domain
        case expires
        case maxAge
        case httpOnly
        case secure
    }

    public init(from decoder: Decoder) throws {
        self.init(name: "", value: "")

        let values = try decoder.container(keyedBy: CodingKeys.self)
        name = try values.decode(String.self, forKey: .name)
        value = try values.decode(String.self, forKey: .value)
        path = try values.decodeIfPresent(String.self, forKey: .path) ?? ""
        domain = try values.decodeIfPresent(String.self, forKey: .domain) ?? ""
        expires = try values.decodeIfPresent(Date.self, forKey: .expires) ?? Date()
        maxAge = try values.decodeIfPresent(Int.self, forKey: .maxAge) ?? nil
        httpOnly = try values.decodeIfPresent(Bool.self, forKey: .httpOnly) ?? false
        secure = try values.decodeIfPresent(Bool.self, forKey: .secure) ?? false
    }

    public func encode(to encoder: Encoder) throws {
        var container = encoder.container(keyedBy: CodingKeys.self)
        try container.encode(name, forKey: .name)
        try container.encode(value, forKey: .value)
        try container.encodeIfPresent(path, forKey: .path)
        try container.encodeIfPresent(domain, forKey: .domain)
        try container.encodeIfPresent(expires, forKey: .expires)
        try container.encodeIfPresent(maxAge, forKey: .maxAge)
        try container.encodeIfPresent(httpOnly, forKey: .httpOnly)
        try container.encodeIfPresent(secure, forKey: .secure)
    }
}
