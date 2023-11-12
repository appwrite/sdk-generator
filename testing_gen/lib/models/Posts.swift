public class Posts {
  public let id: String
  public let collectionId: String
  public let databaseId: String
  public let createdAt: String
  public let updatedAt: String
  public let permissions: Permissions
  public let title: String
  public let text: String
  public let profileId: String
  public let readingTime: String
  public let published: Bool
  public let coverId: String
  public let createdAt: Int

  init(
    id: String,
    collectionId: String,
    databaseId: String,
    createdAt: String,
    updatedAt: String,
    permissions: Permissions,
    title: String,
    text: String,
    profileId: String,
    readingTime: String,
    published: Bool,
    coverId: String,
    createdAt: Int
  ) {
    self.id = id
    self.collectionId = collectionId
    self.databaseId = databaseId
    self.createdAt = createdAt
    self.updatedAt = updatedAt
    self.permissions = permissions
    self.title = title,
    self.text = text,
    self.profileId = profileId,
    self.readingTime = readingTime,
    self.published = published,
    self.coverId = coverId,
    self.createdAt = createdAt
  }

  public static func from(map: [String: Any]) -> Posts {
    return Posts(
        id: map["$id"] as! String,
        collectionId: map["$collectionId"] as! String,
        databaseId: map["$databaseId"] as! String,
        createdAt: map["$id"] as! String,
        updatedAt: map["$id"] as! String,
        permissions: map["$id"] as! Permissions,
        title: map["title"],
      text: map["text"],
      profileId: map["profileId"],
      readingTime: map["readingTime"],
      published: map["published"],
      coverId: map["coverId"],
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
        'title': title,
      'text': text,
      'profileId': profileId,
      'readingTime': readingTime,
      'published': published,
      'coverId': coverId,
      'createdAt': createdAt
    ]
  }
}