
/// Currency
public class Currency {

    /// Currency symbol.
    public let symbol: String

    /// Currency name.
    public let name: String

    /// Currency native symbol.
    public let symbolNative: String

    /// Number of decimal digits.
    public let decimalDigits: Int

    /// Currency digit rounding.
    public let rounding: Double

    /// Currency code in [ISO 4217-1](http://en.wikipedia.org/wiki/ISO_4217) three-character format.
    public let code: String

    /// Currency plural name
    public let namePlural: String

    init(
        symbol: String,
        name: String,
        symbolNative: String,
        decimalDigits: Int,
        rounding: Double,
        code: String,
        namePlural: String
    ) {
        self.symbol = symbol
        self.name = name
        self.symbolNative = symbolNative
        self.decimalDigits = decimalDigits
        self.rounding = rounding
        self.code = code
        self.namePlural = namePlural
    }

    public static func from(map: [String: Any]) -> Currency {
        return Currency(
            symbol: map["symbol"] as! String,
            name: map["name"] as! String,
            symbolNative: map["symbolNative"] as! String,
            decimalDigits: map["decimalDigits"] as! Int,
            rounding: map["rounding"] as! Double,
            code: map["code"] as! String,
            namePlural: map["namePlural"] as! String
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "symbol": symbol as Any,
            "name": name as Any,
            "symbolNative": symbolNative as Any,
            "decimalDigits": decimalDigits as Any,
            "rounding": rounding as Any,
            "code": code as Any,
            "namePlural": namePlural as Any
        ]
    }
                                
}