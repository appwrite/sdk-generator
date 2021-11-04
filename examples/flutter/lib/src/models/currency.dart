part of appwrite.models;

/// Currency
class Currency {
    /// Currency symbol.
    final String symbol;
    /// Currency name.
    final String name;
    /// Currency native symbol.
    final String symbolNative;
    /// Number of decimal digits.
    final int decimalDigits;
    /// Currency digit rounding.
    final double rounding;
    /// Currency code in [ISO 4217-1](http://en.wikipedia.org/wiki/ISO_4217) three-character format.
    final String code;
    /// Currency plural name
    final String namePlural;

    Currency({
        required this.symbol,
        required this.name,
        required this.symbolNative,
        required this.decimalDigits,
        required this.rounding,
        required this.code,
        required this.namePlural,
    });

    factory Currency.fromMap(Map<String, dynamic> map) {
        return Currency(
            symbol: map['symbol'],
            name: map['name'],
            symbolNative: map['symbolNative'],
            decimalDigits: map['decimalDigits'],
            rounding: map['rounding'].toDouble(),
            code: map['code'],
            namePlural: map['namePlural'],
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "symbol": symbol,
            "name": name,
            "symbolNative": symbolNative,
            "decimalDigits": decimalDigits,
            "rounding": rounding,
            "code": code,
            "namePlural": namePlural,
        };
    }
}
