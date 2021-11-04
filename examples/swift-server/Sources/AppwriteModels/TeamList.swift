
/// Teams List
public class TeamList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of teams.
    public let teams: [Team]

    init(
        sum: Int,
        teams: [Team]
    ) {
        self.sum = sum
        self.teams = teams
    }

    public static func from(map: [String: Any]) -> TeamList {
        return TeamList(
            sum: map["sum"] as! Int,
            teams: (map["teams"] as! [[String: Any]]).map { Team.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "teams": teams.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}