
/// Language
public class Language {

    /// Language name.
    public let name: String

    /// Language two-character ISO 639-1 codes.
    public let code: String

    /// Language native name.
    public let nativeName: String

    init(
        name: String,
        code: String,
        nativeName: String
    ) {
        self.name = name
        self.code = code
        self.nativeName = nativeName
    }

    public static func from(map: [String: Any]) -> Language {
        return Language(
            name: map["name"] as! String,
            code: map["code"] as! String,
            nativeName: map["nativeName"] as! String
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "name": name as Any,
            "code": code as Any,
            "nativeName": nativeName as Any
        ]
    }
                
}