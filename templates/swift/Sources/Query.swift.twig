public class Query {

    public static func equal(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "equal", to: value)
    }

    public static func notEqual(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "notEqual", to: value)
    }

    public static func lessThan(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "lessThan", to: value)
    }

    public static func lessThanEqual(attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "lessThanEqual", to: value)
    }

    public static func greaterThan(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "greaterThan", to: value)
    }

    public static func greaterThanEqual(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "greaterThanEqual", to: value)
    }

    public static func isNull(_ attribute: String) -> String {
        "isNull(\"\(attribute)\")"
    }

    public static func isNotNull(_ attribute: String) -> String {
        "isNotNull(\"\(attribute)\")"
    }

    public static func between(_ attribute: String, start: Int, end: Int) -> String {
        "between(\"\(attribute)\", \(start), \(end))"
    }

    public static func between(_ attribute: String, start: Double, end: Double) -> String {
        "between(\"\(attribute)\", \(start), \(end))"
    }

    public static func between(_ attribute: String, start: String, end: String) -> String {
        "between(\"\(attribute)\", \"\(start)\", \"\(end)\")"
    }

    public static func startsWith(_ attribute: String, value: String) -> String {
        buildQueryWhere(attribute, is: "startsWith", to: value)
    }

    public static func endsWith(_ attribute: String, value: String) -> String {
        buildQueryWhere(attribute, is: "endsWith", to: value)
    }

    public static func select(_ attributes: [String]) -> String {
        "select([\(attributes.map { "\"\($0)\"" }.joined(separator: ","))])"
    }

    public static func search(_ attribute: String, value: String) -> String {
        buildQueryWhere(attribute, is: "search", to: value)
    }

    public static func orderAsc(_ attribute: String) -> String {
        "orderAsc(\"\(attribute)\")"
    }

    public static func orderDesc(_ attribute: String) -> String {
        "orderDesc(\"\(attribute)\")"
    }

    public static func cursorBefore(_ id: String) -> String {
        "cursorBefore(\"\(id)\")"
    }

    public static func cursorAfter(_ id: String) -> String {
        "cursorAfter(\"\(id)\")"
    }

    public static func limit(_ limit: Int) -> String {
        "limit(\(limit))"
    }

    public static func offset(_ offset: Int) -> String {
        "offset(\(offset))"
    }

    public static func buildQueryWhere(_ attribute: String, is method: String, to value: Any) -> String {
        switch value {
        case let value as Array<Any>:
            return "\(method)(\"\(attribute)\", [\(value.map { parseValues($0) }.joined(separator: ",") )])"
        default:
            return "\(method)(\"\(attribute)\", [\(parseValues(value))])"
        }
    }

    private static func parseValues(_ value: Any) -> String {
        switch value {
        case let value as String:
            return "\"\(value)\""
        default:
            return "\(value)"
        }
    }
}