import Foundation

enum QueryValue: Codable {
    case string(String)
    case int(Int)
    case double(Double)
    case bool(Bool)
    case query(Query)
    case array([QueryValue])   // for nested arrays

    init(from decoder: Decoder) throws {
        let container = try decoder.singleValueContainer()

        if let stringValue = try? container.decode(String.self) {
            self = .string(stringValue)
        } else if let intValue = try? container.decode(Int.self) {
            self = .int(intValue)
        } else if let doubleValue = try? container.decode(Double.self) {
            self = .double(doubleValue)
        } else if let boolValue = try? container.decode(Bool.self) {
            self = .bool(boolValue)
        } else if let queryValue = try? container.decode(Query.self) {
            self = .query(queryValue)
        } else if let arrayValue = try? container.decode([QueryValue].self) {
            self = .array(arrayValue)
        } else {
            throw DecodingError.dataCorruptedError(
                in: container,
                debugDescription: "QueryValue cannot be decoded"
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
        case .query(let value):
            try container.encode(value)
        case .array(let value):
            try container.encode(value)
        }
    }
}

public struct Query : Codable, CustomStringConvertible {
    var method: String
    var attribute: String?
    var values: [QueryValue]?

    init(method: String, attribute: String? = nil, values: Any? = nil) {
        self.method = method
        self.attribute = attribute
        self.values = Query.convertToQueryValueArray(values)
    }

    public init(from decoder: Decoder) throws {
        let container = try decoder.container(keyedBy: CodingKeys.self)

        self.method = try container.decode(String.self, forKey: .method)
        self.attribute = try container.decodeIfPresent(String.self, forKey: .attribute)
        self.values = try container.decodeIfPresent([QueryValue].self, forKey: .values)
    }

    private static func convertToQueryValueArray(_ values: Any?) -> [QueryValue]? {
        switch values {
        case let valueArray as [QueryValue]:
            return valueArray
        case let stringArray as [String]:
            return stringArray.map { .string($0) }
        case let intArray as [Int]:
            return intArray.map { .int($0) }
        case let doubleArray as [Double]:
            return doubleArray.map { .double($0) }
        case let boolArray as [Bool]:
            return boolArray.map { .bool($0) }
        case let queryArray as [Query]:
            return queryArray.map { .query($0) }
        case let stringValue as String:
            return [.string(stringValue)]
        case let intValue as Int:
            return [.int(intValue)]
        case let doubleValue as Double:
            return [.double(doubleValue)]
        case let boolValue as Bool:
            return [.bool(boolValue)]
        case let queryValue as Query:
            return [.query(queryValue)]
        case let anyArray as [Any]:
            // Handle nested arrays
            let nestedValues = anyArray.compactMap { item -> QueryValue? in
                if let stringValue = item as? String {
                    return .string(stringValue)
                } else if let intValue = item as? Int {
                    return .int(intValue)
                } else if let doubleValue = item as? Double {
                    return .double(doubleValue)
                } else if let boolValue = item as? Bool {
                    return .bool(boolValue)
                } else if let queryValue = item as? Query {
                    return .query(queryValue)
                } else if let nestedArray = item as? [Any] {
                    // Convert nested array to QueryValue.array
                    if let converted = convertToQueryValueArray(nestedArray) {
                        return .array(converted)
                    }
                    return nil
                }
                return nil
            }
            return nestedValues.isEmpty ? nil : nestedValues

        default:
            return nil
        }
    }

    enum CodingKeys: String, CodingKey {
        case method
        case attribute
        case values
    }

    public func encode(to encoder: Encoder) throws {
        var container = encoder.container(keyedBy: CodingKeys.self)
        try container.encode(method, forKey: .method)

        if (self.attribute != nil) {
            try container.encode(attribute, forKey: .attribute)
        }

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

    public static func equal(_ attribute: String, value: Any) -> String {
        return Query(
            method: "equal",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func notEqual(_ attribute: String, value: Any) -> String {
        return Query(
            method: "notEqual",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func lessThan(_ attribute: String, value: Any) -> String {
        return Query(
            method: "lessThan",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func lessThanEqual(attribute: String, value: Any) -> String {
        return Query(
            method: "lessThanEqual",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func greaterThan(_ attribute: String, value: Any) -> String {
        return Query(
            method: "greaterThan",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func greaterThanEqual(_ attribute: String, value: Any) -> String {
        return Query(
            method: "greaterThanEqual",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func isNull(_ attribute: String) -> String {
        return Query(
            method: "isNull",
            attribute: attribute
        ).description
    }

    public static func isNotNull(_ attribute: String) -> String {
        return Query(
            method: "isNotNull",
            attribute: attribute
        ).description
    }

    public static func between(_ attribute: String, start: Int, end: Int) -> String {
        return Query(
            method: "between",
            attribute: attribute,
            values: [start, end]
        ).description
    }

    public static func between(_ attribute: String, start: Double, end: Double) -> String {
        return Query(
            method: "between",
            attribute: attribute,
            values: [start, end]
        ).description
    }

    public static func between(_ attribute: String, start: String, end: String) -> String {
        return Query(
            method: "between",
            attribute: attribute,
            values: [start, end]
        ).description
    }

    public static func startsWith(_ attribute: String, value: String) -> String {
        return Query(
            method: "startsWith",
            attribute: attribute,
            values: [value]
        ).description
    }

    public static func endsWith(_ attribute: String, value: String) -> String {
        return Query(
            method: "endsWith",
            attribute: attribute,
            values: [value]
        ).description
    }

    public static func select(_ attributes: [String]) -> String {
        return Query(
            method: "select",
            values: attributes
        ).description
    }

    public static func search(_ attribute: String, value: String) -> String {
        return Query(
            method: "search",
            attribute: attribute,
            values: [value]
        ).description
    }

    public static func orderAsc(_ attribute: String) -> String {
        return Query(
            method: "orderAsc",
            attribute: attribute
        ).description
    }

    public static func orderDesc(_ attribute: String) -> String {
        return Query(
            method: "orderDesc",
            attribute: attribute
        ).description
    }

    public static func cursorBefore(_ id: String) -> String {
        return Query(
            method: "cursorBefore",
            values: [id]
        ).description
    }

    public static func cursorAfter(_ id: String) -> String {
        return Query(
            method: "cursorAfter",
            values: [id]
        ).description
    }

    public static func limit(_ limit: Int) -> String {
        return Query(
            method: "limit",
            values: [limit]
        ).description
    }

    public static func offset(_ offset: Int) -> String {
        return Query(
            method: "offset",
            values: [offset]
        ).description
    }

    public static func contains(_ attribute: String, value: Any) -> String {
        return Query(
            method: "contains",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func notContains(_ attribute: String, value: Any) -> String {
        return Query(
            method: "notContains",
            attribute: attribute,
            values: Query.parseValue(value)
        ).description
    }

    public static func notSearch(_ attribute: String, value: String) -> String {
        return Query(
            method: "notSearch",
            attribute: attribute,
            values: [value]
        ).description
    }

    public static func notBetween(_ attribute: String, start: Int, end: Int) -> String {
        return Query(
            method: "notBetween",
            attribute: attribute,
            values: [start, end]
        ).description
    }

    public static func notBetween(_ attribute: String, start: Double, end: Double) -> String {
        return Query(
            method: "notBetween",
            attribute: attribute,
            values: [start, end]
        ).description
    }

    public static func notBetween(_ attribute: String, start: String, end: String) -> String {
        return Query(
            method: "notBetween",
            attribute: attribute,
            values: [start, end]
        ).description
    }

    public static func notStartsWith(_ attribute: String, value: String) -> String {
        return Query(
            method: "notStartsWith",
            attribute: attribute,
            values: [value]
        ).description
    }

    public static func notEndsWith(_ attribute: String, value: String) -> String {
        return Query(
            method: "notEndsWith",
            attribute: attribute,
            values: [value]
        ).description
    }

    public static func createdBefore(_ value: String) -> String {
        return Query(
            method: "createdBefore",
            values: [value]
        ).description
    }

    public static func createdAfter(_ value: String) -> String {
        return Query(
            method: "createdAfter",
            values: [value]
        ).description
    }

    public static func createdBetween(_ start: String, _ end: String) -> String {
        return Query(
            method: "createdBetween",
            values: [start, end]
        ).description
    }

    public static func updatedBefore(_ value: String) -> String {
        return Query(
            method: "updatedBefore",
            values: [value]
        ).description
    }

    public static func updatedAfter(_ value: String) -> String {
        return Query(
            method: "updatedAfter",
            values: [value]
        ).description
    }

    public static func updatedBetween(_ start: String, _ end: String) -> String {
        return Query(
            method: "updatedBetween",
            values: [start, end]
        ).description
    }

    public static func or(_ queries: [String]) -> String {
        let decoder = JSONDecoder()
        let decodedQueries = queries.compactMap { queryStr -> Query? in
            guard let data = queryStr.data(using: .utf8) else {
                return nil
            }
            return try? decoder.decode(Query.self, from: data)
        }

        return Query(
            method: "or",
            values: decodedQueries
        ).description
    }

    public static func and(_ queries: [String]) -> String {
        let decoder = JSONDecoder()
        let decodedQueries = queries.compactMap { queryStr -> Query? in
            guard let data = queryStr.data(using: .utf8) else {
                return nil
            }
            return try? decoder.decode(Query.self, from: data)
        }

        return Query(
            method: "and",
            values: decodedQueries
        ).description
    }

    public static func distanceEqual(_ attribute: String, values: [Any], distance: Double, meters: Bool = true) -> String {
        return Query(
            method: "distanceEqual",
            attribute: attribute,
            values: [values, distance, meters]
        ).description
    }

    public static func distanceNotEqual(_ attribute: String, values: [Any], distance: Double, meters: Bool = true) -> String {
        return Query(
            method: "distanceNotEqual",
            attribute: attribute,
            values: [values, distance, meters]
        ).description
    }

    public static func distanceGreaterThan(_ attribute: String, values: [Any], distance: Double, meters: Bool = true) -> String {
        return Query(
            method: "distanceGreaterThan",
            attribute: attribute,
            values: [values, distance, meters]
        ).description
    }

    public static func distanceLessThan(_ attribute: String, values: [Any], distance: Double, meters: Bool = true) -> String {
        return Query(
            method: "distanceLessThan",
            attribute: attribute,
            values: [values, distance, meters]
        ).description
    }

    public static func intersects(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "intersects",
            attribute: attribute,
            values: values
        ).description
    }

    public static func notIntersects(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "notIntersects",
            attribute: attribute,
            values: values
        ).description
    }

    public static func crosses(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "crosses",
            attribute: attribute,
            values: values
        ).description
    }

    public static func notCrosses(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "notCrosses",
            attribute: attribute,
            values: values
        ).description
    }

    public static func overlaps(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "overlaps",
            attribute: attribute,
            values: values
        ).description
    }

    public static func notOverlaps(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "notOverlaps",
            attribute: attribute,
            values: values
        ).description
    }

    public static func touches(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "touches",
            attribute: attribute,
            values: values
        ).description
    }

    public static func notTouches(_ attribute: String, values: [Any]) -> String {
        return Query(
            method: "notTouches",
            attribute: attribute,
            values: values
        ).description
    }

    private static func parseValue(_ value: Any) -> [Any] {
        if let value = value as? [Any] {
            return value
        } else {
            return [value]
        }
    }
}
