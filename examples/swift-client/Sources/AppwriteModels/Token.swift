
/// Token
public class Token {

    /// Token ID.
    public let id: String

    /// User ID.
    public let userId: String

    /// Token secret key. This will return an empty string unless the response is returned using an API key or as part of a webhook payload.
    public let secret: String

    /// Token expiration date in Unix timestamp.
    public let expire: Int

    init(
        id: String,
        userId: String,
        secret: String,
        expire: Int
    ) {
        self.id = id
        self.userId = userId
        self.secret = secret
        self.expire = expire
    }

    public static func from(map: [String: Any]) -> Token {
        return Token(
            id: map["$id"] as! String,
            userId: map["userId"] as! String,
            secret: map["secret"] as! String,
            expire: map["expire"] as! Int
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "userId": userId as Any,
            "secret": secret as Any,
            "expire": expire as Any
        ]
    }
                    
}