
/// Sessions List
public class SessionList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of sessions.
    public let sessions: [Session]

    init(
        sum: Int,
        sessions: [Session]
    ) {
        self.sum = sum
        self.sessions = sessions
    }

    public static func from(map: [String: Any]) -> SessionList {
        return SessionList(
            sum: map["sum"] as! Int,
            sessions: (map["sessions"] as! [[String: Any]]).map { Session.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "sessions": sessions.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}