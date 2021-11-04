
/// Phones List
public class PhoneList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of phones.
    public let phones: [Phone]

    init(
        sum: Int,
        phones: [Phone]
    ) {
        self.sum = sum
        self.phones = phones
    }

    public static func from(map: [String: Any]) -> PhoneList {
        return PhoneList(
            sum: map["sum"] as! Int,
            phones: (map["phones"] as! [[String: Any]]).map { Phone.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "phones": phones.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}