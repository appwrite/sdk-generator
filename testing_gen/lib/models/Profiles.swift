public class Profiles {
  public let id: String
  public let collectionId: String
  public let databaseId: String
  public let createdAt: String
  public let updatedAt: String
  public let permissions: Permissions
  public let name: String
  public let userId: String

  init(
    id: String,
    collectionId: String,
    databaseId: String,
    createdAt: String,
    updatedAt: String,
    permissions: Permissions,
    name: String,
    userId: String
  ) {
    self.id = id
    self.collectionId = collectionId
    self.databaseId = databaseId
    self.createdAt = createdAt
    self.updatedAt = updatedAt
    self.permissions = permissions
    self.name = name,
    self.userId = userId
  }

  public static func from(map: [String: Any]) -> Profiles {
    return Profiles(
        id: map["$id"] as! String,
        collectionId: map["$collectionId"] as! String,
        databaseId: map["$databaseId"] as! String,
        createdAt: map["$id"] as! String,
        updatedAt: map["$id"] as! String,
        permissions: map["$id"] as! Permissions,
        name: map["name"],
      userId: map["userId"]
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
        'name': name,
      'userId': userId
    ]
  }
}