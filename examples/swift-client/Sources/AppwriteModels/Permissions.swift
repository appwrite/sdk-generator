
/// Permissions
public class Permissions {

    /// Read permissions.
    public let read: [Any]

    /// Write permissions.
    public let write: [Any]

    init(
        read: [Any],
        write: [Any]
    ) {
        self.read = read
        self.write = write
    }

    public static func from(map: [String: Any]) -> Permissions {
        return Permissions(
            read: map["read"] as! [Any],
            write: map["write"] as! [Any]
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "read": read as Any,
            "write": write as Any
        ]
    }
            
}