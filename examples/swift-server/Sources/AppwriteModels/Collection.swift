
/// Collection
public class Collection {

    /// Collection ID.
    public let id: String

    /// Collection permissions.
    public let permissions: Permissions

    /// Collection name.
    public let name: String

    /// Collection creation date in Unix timestamp.
    public let dateCreated: Int

    /// Collection creation date in Unix timestamp.
    public let dateUpdated: Int

    /// Collection rules.
    public let rules: [Rule]

    init(
        id: String,
        permissions: Permissions,
        name: String,
        dateCreated: Int,
        dateUpdated: Int,
        rules: [Rule]
    ) {
        self.id = id
        self.permissions = permissions
        self.name = name
        self.dateCreated = dateCreated
        self.dateUpdated = dateUpdated
        self.rules = rules
    }

    public static func from(map: [String: Any]) -> Collection {
        return Collection(
            id: map["$id"] as! String,
            permissions: Permissions.from(map: map["$permissions"] as! [String: Any]),
            name: map["name"] as! String,
            dateCreated: map["dateCreated"] as! Int,
            dateUpdated: map["dateUpdated"] as! Int,
            rules: (map["rules"] as! [[String: Any]]).map { Rule.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "$permissions": permissions.toMap() as Any,
            "name": name as Any,
            "dateCreated": dateCreated as Any,
            "dateUpdated": dateUpdated as Any,
            "rules": rules.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                                                                                                                                                                                            
}