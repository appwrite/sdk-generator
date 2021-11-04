
/// Continent
public class Continent {

    /// Continent name.
    public let name: String

    /// Continent two letter code.
    public let code: String

    init(
        name: String,
        code: String
    ) {
        self.name = name
        self.code = code
    }

    public static func from(map: [String: Any]) -> Continent {
        return Continent(
            name: map["name"] as! String,
            code: map["code"] as! String
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "name": name as Any,
            "code": code as Any
        ]
    }
            
}