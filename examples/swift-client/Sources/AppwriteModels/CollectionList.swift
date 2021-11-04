
/// Collections List
public class CollectionList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of collections.
    public let collections: [Collection]

    init(
        sum: Int,
        collections: [Collection]
    ) {
        self.sum = sum
        self.collections = collections
    }

    public static func from(map: [String: Any]) -> CollectionList {
        return CollectionList(
            sum: map["sum"] as! Int,
            collections: (map["collections"] as! [[String: Any]]).map { Collection.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "collections": collections.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}