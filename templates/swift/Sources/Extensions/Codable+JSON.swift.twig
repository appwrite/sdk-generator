import Foundation

let jsonEncoder = JSONEncoder()
let jsonDecoder = JSONDecoder()

extension Encodable {
    func toJson() throws -> String {
        return String(data: try jsonEncoder.encode(self), encoding: .utf8)!
    }
}

extension String {
    func fromJson<T : Decodable>(to model: T.Type) throws -> T {
        return try jsonDecoder.decode(model, from: self.data(using: .utf8)!)
    }
}

protocol JsonConvert : Encodable {
    func jsonCast<T>() -> T
}

extension JsonConvert {
    func jsonCast<T : Decodable>(to model: T.Type) throws -> T {
        let string = try (self as Encodable).toJson()
        return try string.fromJson(to: model)
    }
}