
/// Locale
public class Locale {

    /// User IP address.
    public let ip: String

    /// Country code in [ISO 3166-1](http://en.wikipedia.org/wiki/ISO_3166-1) two-character format
    public let countryCode: String

    /// Country name. This field support localization.
    public let country: String

    /// Continent code. A two character continent code &quot;AF&quot; for Africa, &quot;AN&quot; for Antarctica, &quot;AS&quot; for Asia, &quot;EU&quot; for Europe, &quot;NA&quot; for North America, &quot;OC&quot; for Oceania, and &quot;SA&quot; for South America.
    public let continentCode: String

    /// Continent name. This field support localization.
    public let continent: String

    /// True if country is part of the Europian Union.
    public let eu: Bool

    /// Currency code in [ISO 4217-1](http://en.wikipedia.org/wiki/ISO_4217) three-character format
    public let currency: String

    init(
        ip: String,
        countryCode: String,
        country: String,
        continentCode: String,
        continent: String,
        eu: Bool,
        currency: String
    ) {
        self.ip = ip
        self.countryCode = countryCode
        self.country = country
        self.continentCode = continentCode
        self.continent = continent
        self.eu = eu
        self.currency = currency
    }

    public static func from(map: [String: Any]) -> Locale {
        return Locale(
            ip: map["ip"] as! String,
            countryCode: map["countryCode"] as! String,
            country: map["country"] as! String,
            continentCode: map["continentCode"] as! String,
            continent: map["continent"] as! String,
            eu: map["eu"] as! Bool,
            currency: map["currency"] as! String
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "ip": ip as Any,
            "countryCode": countryCode as Any,
            "country": country as Any,
            "continentCode": continentCode as Any,
            "continent": continent as Any,
            "eu": eu as Any,
            "currency": currency as Any
        ]
    }
                                
}