
/// Logs List
public class LogList {

    /// List of logs.
    public let logs: [Log]

    init(
        logs: [Log]
    ) {
        self.logs = logs
    }

    public static func from(map: [String: Any]) -> LogList {
        return LogList(
            logs: (map["logs"] as! [[String: Any]]).map { Log.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "logs": logs.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                
}