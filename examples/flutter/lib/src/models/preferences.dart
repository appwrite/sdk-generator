part of appwrite.models;

/// Preferences
class Preferences {
    final Map<String, dynamic> data;

    Preferences({
        required this.data,
    });

    factory Preferences.fromMap(Map<String, dynamic> map) {
        return Preferences(
            data: map,
        );
    }

    Map<String, dynamic> toMap() {
        return {
            "data": data,
        };
    }

    T convertTo<T>(T Function(Map) fromJson) => fromJson(data);
}
