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
        path = try values.decode(String.self, forKey: .path)
        domain = try values.decode(String.self, forKey: .domain)
        expires = try values.decode(Date.self, forKey: .expires)
        value = try values.decode(String.self, forKey: .value)
        maxAge = try values.decodeIfPresent(Int.self, forKey: .maxAge) ?? nil
        httpOnly = try values.decodeIfPresent(Bool.self, forKey: .httpOnly) ?? false
        secure = try values.decodeIfPresent(Bool.self, forKey: .secure) ?? false
    }

    public func encode(to encoder: Encoder) throws {
        var container = encoder.container(keyedBy: CodingKeys.self)
        try container.encode(name, forKey: .name)
        try container.encode(value, forKey: .value)
        try container.encode(path, forKey: .path)
        try container.encode(domain, forKey: .domain)
        try container.encode(expires, forKey: .expires)
        try container.encodeIfPresent(maxAge, forKey: .maxAge)
        try container.encodeIfPresent(httpOnly, forKey: .httpOnly)
        try container.encodeIfPresent(secure, forKey: .secure)
    }
}
