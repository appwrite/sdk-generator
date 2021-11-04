
/// Preferences
public class Preferences {

    let data: [String: Any]

    init(
        data: [String: Any]
    ) {
        self.data = data
    }

    public static func from(map: [String: Any]) -> Preferences {
        return Preferences(
            data: map
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "data": data
        ]
    }

    public func convertTo<T>(fromJson: ([String: Any]) -> T) -> T {
        return fromJson(data)
    }
        
}