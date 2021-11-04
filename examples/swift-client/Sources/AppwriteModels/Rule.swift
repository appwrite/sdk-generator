
/// Rule
public class Rule {

    /// Rule ID.
    public let id: String

    /// Rule Collection.
    public let collection: String

    /// Rule type. Possible values: 
    public let type: String

    /// Rule key.
    public let key: String

    /// Rule label.
    public let label: String

    /// Rule default value.
    public let xdefault: String

    /// Is array?
    public let array: Bool

    /// Is required?
    public let xrequired: Bool

    /// List of allowed values
    public let list: [Any]

    init(
        id: String,
        collection: String,
        type: String,
        key: String,
        label: String,
        xdefault: String,
        array: Bool,
        xrequired: Bool,
        list: [Any]
    ) {
        self.id = id
        self.collection = collection
        self.type = type
        self.key = key
        self.label = label
        self.xdefault = xdefault
        self.array = array
        self.xrequired = xrequired
        self.list = list
    }

    public static func from(map: [String: Any]) -> Rule {
        return Rule(
            id: map["$id"] as! String,
            collection: map["$collection"] as! String,
            type: map["type"] as! String,
            key: map["key"] as! String,
            label: map["label"] as! String,
            xdefault: map["default"] as! String,
            array: map["array"] as! Bool,
            xrequired: map["required"] as! Bool,
            list: map["list"] as! [Any]
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "$collection": collection as Any,
            "type": type as Any,
            "key": key as Any,
            "label": label as Any,
            "xdefault": xdefault as Any,
            "array": array as Any,
            "xrequired": xrequired as Any,
            "list": list as Any
        ]
    }
                                        
}