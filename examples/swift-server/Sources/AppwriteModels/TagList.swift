
/// Tags List
public class TagList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of tags.
    public let tags: [Tag]

    init(
        sum: Int,
        tags: [Tag]
    ) {
        self.sum = sum
        self.tags = tags
    }

    public static func from(map: [String: Any]) -> TagList {
        return TagList(
            sum: map["sum"] as! Int,
            tags: (map["tags"] as! [[String: Any]]).map { Tag.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "tags": tags.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}