
/// Currencies List
public class CurrencyList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of currencies.
    public let currencies: [Currency]

    init(
        sum: Int,
        currencies: [Currency]
    ) {
        self.sum = sum
        self.currencies = currencies
    }

    public static func from(map: [String: Any]) -> CurrencyList {
        return CurrencyList(
            sum: map["sum"] as! Int,
            currencies: (map["currencies"] as! [[String: Any]]).map { Currency.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "currencies": currencies.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}