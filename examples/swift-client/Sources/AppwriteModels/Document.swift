
/// Document
public class Document {

    /// Document ID.
    public let id: String

    /// Collection ID.
    public let collection: String

    /// Document permissions.
    public let permissions: Permissions

    let data: [String: Any]

    init(
        id: String,
        collection: String,
        permissions: Permissions,
        data: [String: Any]
    ) {
        self.id = id
        self.collection = collection
        self.permissions = permissions
        self.data = data
    }

    public static func from(map: [String: Any]) -> Document {
        return Document(
            id: map["$id"] as! String,
            collection: map["$collection"] as! String,
            permissions: Permissions.from(map: map["$permissions"] as! [String: Any]),
            data: map
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "$collection": collection as Any,
            "$permissions": permissions.toMap() as Any,
            "data": data
        ]
    }

    public func convertTo<T>(fromJson: ([String: Any]) -> T) -> T {
        return fromJson(data)
    }
                                                                                                                                                                            
}