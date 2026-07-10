import Foundation

public enum Condition: String, Codable {
    case equal = "equal"
    case notEqual = "notEqual"
    case greaterThan = "greaterThan"
    case greaterThanEqual = "greaterThanEqual"
    case lessThan = "lessThan"
    case lessThanEqual = "lessThanEqual"
    case contains = "contains"
    case isNull = "isNull"
    case isNotNull = "isNotNull"
}

enum OperatorValue: Codable {
    case string(String)
    case int(Int)
    case double(Double)
    case bool(Bool)
    case array([OperatorValue])
    case null

    init(from decoder: Decoder) throws {
        let container = try decoder.singleValueContainer()

        if container.decodeNil() {
            self = .null
        } else if let stringValue = try? container.decode(String.self) {
            self = .string(stringValue)
        } else if let intValue = try? container.decode(Int.self) {
            self = .int(intValue)
        } else if let doubleValue = try? container.decode(Double.self) {
            self = .double(doubleValue)
        } else if let boolValue = try? container.decode(Bool.self) {
            self = .bool(boolValue)
        } else if let arrayValue = try? container.decode([OperatorValue].self) {
            self = .array(arrayValue)
        } else {
            throw DecodingError.dataCorruptedError(
                in: container,
                debugDescription: "OperatorValue cannot be decoded"
            )
        }
    }

    func encode(to encoder: Encoder) throws {
        var container = encoder.singleValueContainer()
        switch self {
        case .string(let value):
            try container.encode(value)
        case .int(let value):
            try container.encode(value)
        case .double(let value):
            try container.encode(value)
        case .bool(let value):
            try container.encode(value)
        case .array(let value):
            try container.encode(value)
        case .null:
            try container.encodeNil()
        }
    }
}

public struct Operator : Codable, CustomStringConvertible {
    var method: String
    var values: [OperatorValue]?

    init(method: String, values: Any? = nil) {
        self.method = method
        self.values = Operator.convertToOperatorValueArray(values)
    }

    public init(from decoder: Decoder) throws {
        let container = try decoder.container(keyedBy: CodingKeys.self)

        self.method = try container.decode(String.self, forKey: .method)
        self.values = try container.decodeIfPresent([OperatorValue].self, forKey: .values)
    }

    private static func convertToOperatorValueArray(_ values: Any?) -> [OperatorValue]? {
        // Handle nil
        if values == nil {
            return nil
        }

        // Handle NSNull as [.null]
        if values is NSNull {
            return [.null]
        }

        switch values {
        case let valueArray as [OperatorValue]:
            return valueArray
        case let stringArray as [String]:
            return stringArray.map { .string($0) }
        case let intArray as [Int]:
            return intArray.map { .int($0) }
        case let doubleArray as [Double]:
            return doubleArray.map { .double($0) }
        case let boolArray as [Bool]:
            return boolArray.map { .bool($0) }
        case let stringValue as String:
            return [.string(stringValue)]
        case let intValue as Int:
            return [.int(intValue)]
        case let doubleValue as Double:
            return [.double(doubleValue)]
        case let boolValue as Bool:
            return [.bool(boolValue)]
        case let anyArray as [Any]:
            // Preserve empty arrays as empty OperatorValue arrays
            if anyArray.isEmpty {
                return []
            }

            // Map all items, converting nil/unknown to .null
            let nestedValues = anyArray.map { item -> OperatorValue in
                if item is NSNull {
                    return .null
                } else if let stringValue = item as? String {
                    return .string(stringValue)
                } else if let intValue = item as? Int {
                    return .int(intValue)
                } else if let doubleValue = item as? Double {
                    return .double(doubleValue)
                } else if let boolValue = item as? Bool {
                    return .bool(boolValue)
                } else if let nestedArray = item as? [Any] {
                    let converted = convertToOperatorValueArray(nestedArray) ?? []
                    return .array(converted)
                } else {
                    // Unknown/unsupported types become .null
                    return .null
                }
            }
            return nestedValues
        default:
            // Unknown types become [.null]
            return [.null]
        }
    }

    enum CodingKeys: String, CodingKey {
        case method
        case values
    }

    public func encode(to encoder: Encoder) throws {
        var container = encoder.container(keyedBy: CodingKeys.self)
        try container.encode(method, forKey: .method)

        if (values != nil) {
            try container.encode(values, forKey: .values)
        }
    }

    public var description: String {
        guard let data = try? JSONEncoder().encode(self) else {
            return ""
        }

        return String(data: data, encoding: .utf8) ?? ""
    }

    public static func increment(_ value: Double = 1, max: Double? = nil) -> String {
        if value.isNaN || value.isInfinite {
            fatalError("Value cannot be NaN or Infinity")
        }
        if let max = max, max.isNaN || max.isInfinite {
            fatalError("Max cannot be NaN or Infinity")
        }
        var values: [Any] = [value]
        if let max = max {
            values.append(max)
        }
        return Operator(method: "increment", values: values).description
    }

    public static func decrement(_ value: Double = 1, min: Double? = nil) -> String {
        if value.isNaN || value.isInfinite {
            fatalError("Value cannot be NaN or Infinity")
        }
        if let min = min, min.isNaN || min.isInfinite {
            fatalError("Min cannot be NaN or Infinity")
        }
        var values: [Any] = [value]
        if let min = min {
            values.append(min)
        }
        return Operator(method: "decrement", values: values).description
    }

    public static func multiply(_ factor: Double, max: Double? = nil) -> String {
        if factor.isNaN || factor.isInfinite {
            fatalError("Factor cannot be NaN or Infinity")
        }
        if let max = max, max.isNaN || max.isInfinite {
            fatalError("Max cannot be NaN or Infinity")
        }
        var values: [Any] = [factor]
        if let max = max {
            values.append(max)
        }
        return Operator(method: "multiply", values: values).description
    }

    public static func divide(_ divisor: Double, min: Double? = nil) -> String {
        if divisor.isNaN || divisor.isInfinite {
            fatalError("Divisor cannot be NaN or Infinity")
        }
        if let min = min, min.isNaN || min.isInfinite {
            fatalError("Min cannot be NaN or Infinity")
        }
        if divisor == 0 {
            fatalError("Divisor cannot be zero")
        }
        var values: [Any] = [divisor]
        if let min = min {
            values.append(min)
        }
        return Operator(method: "divide", values: values).description
    }

    public static func modulo(_ divisor: Double) -> String {
        if divisor.isNaN || divisor.isInfinite {
            fatalError("Divisor cannot be NaN or Infinity")
        }
        if divisor == 0 {
            fatalError("Divisor cannot be zero")
        }
        return Operator(method: "modulo", values: [divisor]).description
    }

    public static func power(_ exponent: Double, max: Double? = nil) -> String {
        if exponent.isNaN || exponent.isInfinite {
            fatalError("Exponent cannot be NaN or Infinity")
        }
        if let max = max, max.isNaN || max.isInfinite {
            fatalError("Max cannot be NaN or Infinity")
        }
        var values: [Any] = [exponent]
        if let max = max {
            values.append(max)
        }
        return Operator(method: "power", values: values).description
    }

    public static func arrayAppend(_ values: [Any]) -> String {
        return Operator(method: "arrayAppend", values: values).description
    }

    public static func arrayPrepend(_ values: [Any]) -> String {
        return Operator(method: "arrayPrepend", values: values).description
    }

    public static func arrayInsert(_ index: Int, value: Any) -> String {
        return Operator(method: "arrayInsert", values: [index, value]).description
    }

    public static func arrayRemove(_ value: Any) -> String {
        return Operator(method: "arrayRemove", values: [value]).description
    }

    public static func arrayUnique() -> String {
        return Operator(method: "arrayUnique", values: []).description
    }

    public static func arrayIntersect(_ values: [Any]) -> String {
        return Operator(method: "arrayIntersect", values: values).description
    }

    public static func arrayDiff(_ values: [Any]) -> String {
        return Operator(method: "arrayDiff", values: values).description
    }

    public static func arrayFilter(_ condition: Condition, value: Any? = nil) -> String {
        let values: [Any] = [condition.rawValue, value ?? NSNull()]
        return Operator(method: "arrayFilter", values: values).description
    }

    public static func stringConcat(_ value: Any) -> String {
        return Operator(method: "stringConcat", values: [value]).description
    }

    public static func stringReplace(_ search: String, _ replace: String) -> String {
        return Operator(method: "stringReplace", values: [search, replace]).description
    }

    public static func toggle() -> String {
        return Operator(method: "toggle", values: []).description
    }

    public static func dateAddDays(_ days: Int) -> String {
        return Operator(method: "dateAddDays", values: [days]).description
    }

    public static func dateSubDays(_ days: Int) -> String {
        return Operator(method: "dateSubDays", values: [days]).description
    }

    public static func dateSetNow() -> String {
        return Operator(method: "dateSetNow", values: []).description
    }
}
