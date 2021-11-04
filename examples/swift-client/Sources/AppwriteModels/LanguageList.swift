
/// Languages List
public class LanguageList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of languages.
    public let languages: [Language]

    init(
        sum: Int,
        languages: [Language]
    ) {
        self.sum = sum
        self.languages = languages
    }

    public static func from(map: [String: Any]) -> LanguageList {
        return LanguageList(
            sum: map["sum"] as! Int,
            languages: (map["languages"] as! [[String: Any]]).map { Language.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "languages": languages.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}