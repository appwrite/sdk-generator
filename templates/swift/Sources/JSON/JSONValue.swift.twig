import Foundation

public enum JSONValue: Codable, Equatable {
    case string(String)
    case int(Int)
    case double(Double)
    case bool(Bool)
    case object([String: JSONValue])
    case array([JSONValue])

    public init(from decoder: Decoder) throws {
        let container = try decoder.singleValueContainer()
        self = try ((try? container.decode(String.self)).map(JSONValue.string))
            .or((try? container.decode(Int.self)).map(JSONValue.int))
            .or((try? container.decode(Double.self)).map(JSONValue.double))
            .or((try? container.decode(Bool.self)).map(JSONValue.bool))
            .or((try? container.decode([String: JSONValue].self)).map(JSONValue.object))
            .or((try? container.decode([JSONValue].self)).map(JSONValue.array))
            .resolve(with: DecodingError.typeMismatch(JSONValue.self, DecodingError.Context(
                codingPath: container.codingPath,
                debugDescription: "Invalid JSON"
            )))
    }
    
    public func encode(to encoder: Encoder) throws {
        var container = encoder.singleValueContainer()
        switch self {
        case .string(let string): try! container.encode(string)
        case .int(let int): try! container.encode(int)
        case .double(let double): try! container.encode(double)
        case .bool(let bool): try! container.encode(bool)
        case .object(let dict): try! container.encode(dict)
        case .array(let array): try! container.encode(array)
        }
    }
}
