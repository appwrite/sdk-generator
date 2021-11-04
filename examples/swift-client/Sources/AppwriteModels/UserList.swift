
/// Users List
public class UserList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of users.
    public let users: [User]

    init(
        sum: Int,
        users: [User]
    ) {
        self.sum = sum
        self.users = users
    }

    public static func from(map: [String: Any]) -> UserList {
        return UserList(
            sum: map["sum"] as! Int,
            users: (map["users"] as! [[String: Any]]).map { User.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "users": users.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}