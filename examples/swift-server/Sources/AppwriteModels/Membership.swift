
/// Membership
public class Membership {

    /// Membership ID.
    public let id: String

    /// User ID.
    public let userId: String

    /// Team ID.
    public let teamId: String

    /// User name.
    public let name: String

    /// User email address.
    public let email: String

    /// Date, the user has been invited to join the team in Unix timestamp.
    public let invited: Int

    /// Date, the user has accepted the invitation to join the team in Unix timestamp.
    public let joined: Int

    /// User confirmation status, true if the user has joined the team or false otherwise.
    public let confirm: Bool

    /// User list of roles
    public let roles: [Any]

    init(
        id: String,
        userId: String,
        teamId: String,
        name: String,
        email: String,
        invited: Int,
        joined: Int,
        confirm: Bool,
        roles: [Any]
    ) {
        self.id = id
        self.userId = userId
        self.teamId = teamId
        self.name = name
        self.email = email
        self.invited = invited
        self.joined = joined
        self.confirm = confirm
        self.roles = roles
    }

    public static func from(map: [String: Any]) -> Membership {
        return Membership(
            id: map["$id"] as! String,
            userId: map["userId"] as! String,
            teamId: map["teamId"] as! String,
            name: map["name"] as! String,
            email: map["email"] as! String,
            invited: map["invited"] as! Int,
            joined: map["joined"] as! Int,
            confirm: map["confirm"] as! Bool,
            roles: map["roles"] as! [Any]
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "userId": userId as Any,
            "teamId": teamId as Any,
            "name": name as Any,
            "email": email as Any,
            "invited": invited as Any,
            "joined": joined as Any,
            "confirm": confirm as Any,
            "roles": roles as Any
        ]
    }
                                        
}