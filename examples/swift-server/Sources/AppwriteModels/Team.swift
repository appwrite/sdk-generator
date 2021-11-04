
/// Team
public class Team {

    /// Team ID.
    public let id: String

    /// Team name.
    public let name: String

    /// Team creation date in Unix timestamp.
    public let dateCreated: Int

    /// Total sum of team members.
    public let sum: Int

    init(
        id: String,
        name: String,
        dateCreated: Int,
        sum: Int
    ) {
        self.id = id
        self.name = name
        self.dateCreated = dateCreated
        self.sum = sum
    }

    public static func from(map: [String: Any]) -> Team {
        return Team(
            id: map["$id"] as! String,
            name: map["name"] as! String,
            dateCreated: map["dateCreated"] as! Int,
            sum: map["sum"] as! Int
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "name": name as Any,
            "dateCreated": dateCreated as Any,
            "sum": sum as Any
        ]
    }
                    
}