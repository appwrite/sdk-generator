
/// Memberships List
public class MembershipList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of memberships.
    public let memberships: [Membership]

    init(
        sum: Int,
        memberships: [Membership]
    ) {
        self.sum = sum
        self.memberships = memberships
    }

    public static func from(map: [String: Any]) -> MembershipList {
        return MembershipList(
            sum: map["sum"] as! Int,
            memberships: (map["memberships"] as! [[String: Any]]).map { Membership.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "memberships": memberships.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}