
/// File
public class File {

    /// File ID.
    public let id: String

    /// File permissions.
    public let permissions: Permissions

    /// File name.
    public let name: String

    /// File creation date in Unix timestamp.
    public let dateCreated: Int

    /// File MD5 signature.
    public let signature: String

    /// File mime type.
    public let mimeType: String

    /// File original size in bytes.
    public let sizeOriginal: Int

    init(
        id: String,
        permissions: Permissions,
        name: String,
        dateCreated: Int,
        signature: String,
        mimeType: String,
        sizeOriginal: Int
    ) {
        self.id = id
        self.permissions = permissions
        self.name = name
        self.dateCreated = dateCreated
        self.signature = signature
        self.mimeType = mimeType
        self.sizeOriginal = sizeOriginal
    }

    public static func from(map: [String: Any]) -> File {
        return File(
            id: map["$id"] as! String,
            permissions: Permissions.from(map: map["$permissions"] as! [String: Any]),
            name: map["name"] as! String,
            dateCreated: map["dateCreated"] as! Int,
            signature: map["signature"] as! String,
            mimeType: map["mimeType"] as! String,
            sizeOriginal: map["sizeOriginal"] as! Int
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "$permissions": permissions.toMap() as Any,
            "name": name as Any,
            "dateCreated": dateCreated as Any,
            "signature": signature as Any,
            "mimeType": mimeType as Any,
            "sizeOriginal": sizeOriginal as Any
        ]
    }
                                                                                                                                                                                        
}