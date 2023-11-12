public class Comments {
  public let id: String
  public let collectionId: String
  public let databaseId: String
  public let createdAt: String
  public let updatedAt: String
  public let permissions: Permissions
  public let text: String
  public let postId: String
  public let profileId: String
  public let createdAt: Int

  init(
    id: String,
    collectionId: String,
    databaseId: String,
    createdAt: String,
    updatedAt: String,
    permissions: Permissions,
    text: String,
    postId: String,
    profileId: String,
    createdAt: Int
  ) {
    self.id = id
    self.collectionId = collectionId
    self.databaseId = databaseId
    self.createdAt = createdAt
    self.updatedAt = updatedAt
    self.permissions = permissions
    self.text = text,
    self.postId = postId,
    self.profileId = profileId,
    self.createdAt = createdAt
  }

  public static func from(map: [String: Any]) -> Comments {
    return Comments(
        id: map["$id"] as! String,
        collectionId: map["$collectionId"] as! String,
        databaseId: map["$databaseId"] as! String,
        createdAt: map["$id"] as! String,
        updatedAt: map["$id"] as! String,
        permissions: map["$id"] as! Permissions,
        text: map["text"],
      postId: map["postId"],
      profileId: map["profileId"],
      createdAt: map["createdAt"]
    )
  }

  public func toMap() -> [String: Any] {
    return [
        "id": id as Any,
        "collectionId": collectionId as Any,
        "databaseId": databaseId as Any,
        "createdAt": createdAt as Any,
        "updatedAt": updatedAt as Any,
        "permissions": permissions.toMap() as Any,
        'text': text,
      'postId': postId,
      'profileId': profileId,
      'createdAt': createdAt
    ]
  }
}