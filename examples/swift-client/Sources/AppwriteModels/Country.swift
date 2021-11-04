
/// Country
public class Country {

    /// Country name.
    public let name: String

    /// Country two-character ISO 3166-1 alpha code.
    public let code: String

    init(
        name: String,
        code: String
    ) {
        self.name = name
        self.code = code
    }

    public static func from(map: [String: Any]) -> Country {
        return Country(
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