
/// Phone
public class Phone {

    /// Phone code.
    public let code: String

    /// Country two-character ISO 3166-1 alpha code.
    public let countryCode: String

    /// Country name.
    public let countryName: String

    init(
        code: String,
        countryCode: String,
        countryName: String
    ) {
        self.code = code
        self.countryCode = countryCode
        self.countryName = countryName
    }

    public static func from(map: [String: Any]) -> Phone {
        return Phone(
            code: map["code"] as! String,
            countryCode: map["countryCode"] as! String,
            countryName: map["countryName"] as! String
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "code": code as Any,
            "countryCode": countryCode as Any,
            "countryName": countryName as Any
        ]
    }
                
}