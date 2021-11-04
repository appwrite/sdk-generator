
/// Tag
public class Tag {

    /// Tag ID.
    public let id: String

    /// Function ID.
    public let functionId: String

    /// The tag creation date in Unix timestamp.
    public let dateCreated: Int

    /// The entrypoint command in use to execute the tag code.
    public let command: String

    /// The code size in bytes.
    public let size: String

    init(
        id: String,
        functionId: String,
        dateCreated: Int,
        command: String,
        size: String
    ) {
        self.id = id
        self.functionId = functionId
        self.dateCreated = dateCreated
        self.command = command
        self.size = size
    }

    public static func from(map: [String: Any]) -> Tag {
        return Tag(
            id: map["$id"] as! String,
            functionId: map["functionId"] as! String,
            dateCreated: map["dateCreated"] as! Int,
            command: map["command"] as! String,
            size: map["size"] as! String
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "functionId": functionId as Any,
            "dateCreated": dateCreated as Any,
            "command": command as Any,
            "size": size as Any
        ]
    }
                        
}