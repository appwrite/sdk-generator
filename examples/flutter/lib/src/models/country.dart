part of appwrite.models;

/// Country
class Country {
    /// Country name.
    final String name;
    /// Country two-character ISO 3166-1 alpha code.
    final String code;

    Country({
        required this.name,
        required this.code,
    });

    factory Country.fromMap(Map<String, dynamic> map) {
        return Country(
            name: map['name'],
            code: map['code'],
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "name": name,
            "code": code,
        };
    }
}
