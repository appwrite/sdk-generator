
/// User
public class User {

    /// User ID.
    public let id: String

    /// User name.
    public let name: String

    /// User registration date in Unix timestamp.
    public let registration: Int

    /// User status. 0 for Unactivated, 1 for active and 2 is blocked.
    public let status: Int

    /// Unix timestamp of the most recent password update
    public let passwordUpdate: Int

    /// User email address.
    public let email: String

    /// Email verification status.
    public let emailVerification: Bool

    /// User preferences as a key-value object
    public let prefs: Preferences

    init(
        id: String,
        name: String,
        registration: Int,
        status: Int,
        passwordUpdate: Int,
        email: String,
        emailVerification: Bool,
        prefs: Preferences
    ) {
        self.id = id
        self.name = name
        self.registration = registration
        self.status = status
        self.passwordUpdate = passwordUpdate
        self.email = email
        self.emailVerification = emailVerification
        self.prefs = prefs
    }

    public static func from(map: [String: Any]) -> User {
        return User(
            id: map["$id"] as! String,
            name: map["name"] as! String,
            registration: map["registration"] as! Int,
            status: map["status"] as! Int,
            passwordUpdate: map["passwordUpdate"] as! Int,
            email: map["email"] as! String,
            emailVerification: map["emailVerification"] as! Bool,
            prefs: Preferences.from(map: map["prefs"] as! [String: Any])
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "name": name as Any,
            "registration": registration as Any,
            "status": status as Any,
            "passwordUpdate": passwordUpdate as Any,
            "email": email as Any,
            "emailVerification": emailVerification as Any,
            "prefs": prefs.toMap() as Any
        ]
    }
                                                                                                                                                                                            
}