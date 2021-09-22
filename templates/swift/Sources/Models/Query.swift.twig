public class Query {

    public static func equal(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "equal", to: value)
    }

    public static func notEqual(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "notEqual", to: value)
    }

    public static func lesser(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "lesser", to: value)
    }

    public static func lesserEqual(attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "lesserEqual", to: value)
    }

    public static func greater(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "greater", to: value)
    }

    public static func greaterEqual(_ attribute: String, value: Any) -> String {
        buildQueryWhere(attribute, is: "greaterEqual", to: value)
    }

    public static func contains(_ attribute: String, value: Array<Any>) -> String {
        buildQueryWhere(attribute, is: "contains", to: value)
    }

    public static func buildQueryWhere(_ attribute: String, is oper: String, to value: Any) -> String {
        switch value {
        case let value as Array<Any>:
            return "\(attribute).\(oper)(\(value.map { parseValues($0) }.joined(separator: ",") ))"
        default:
            return "\(attribute).\(oper)(\(parseValues(value))"
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