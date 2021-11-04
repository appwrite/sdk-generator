
/// Function
public class Function {

    /// Function ID.
    public let id: String

    /// Function permissions.
    public let permissions: Permissions

    /// Function name.
    public let name: String

    /// Function creation date in Unix timestamp.
    public let dateCreated: Int

    /// Function update date in Unix timestamp.
    public let dateUpdated: Int

    /// Function status. Possible values: disabled, enabled
    public let status: String

    /// Function execution runtime.
    public let runtime: String

    /// Function active tag ID.
    public let tag: String

    /// Function environment variables.
    public let vars: String

    /// Function trigger events.
    public let events: [Any]

    /// Function execution schedult in CRON format.
    public let schedule: String

    /// Function next scheduled execution date in Unix timestamp.
    public let scheduleNext: Int

    /// Function next scheduled execution date in Unix timestamp.
    public let schedulePrevious: Int

    /// Function execution timeout in seconds.
    public let timeout: Int

    init(
        id: String,
        permissions: Permissions,
        name: String,
        dateCreated: Int,
        dateUpdated: Int,
        status: String,
        runtime: String,
        tag: String,
        vars: String,
        events: [Any],
        schedule: String,
        scheduleNext: Int,
        schedulePrevious: Int,
        timeout: Int
    ) {
        self.id = id
        self.permissions = permissions
        self.name = name
        self.dateCreated = dateCreated
        self.dateUpdated = dateUpdated
        self.status = status
        self.runtime = runtime
        self.tag = tag
        self.vars = vars
        self.events = events
        self.schedule = schedule
        self.scheduleNext = scheduleNext
        self.schedulePrevious = schedulePrevious
        self.timeout = timeout
    }

    public static func from(map: [String: Any]) -> Function {
        return Function(
            id: map["$id"] as! String,
            permissions: Permissions.from(map: map["$permissions"] as! [String: Any]),
            name: map["name"] as! String,
            dateCreated: map["dateCreated"] as! Int,
            dateUpdated: map["dateUpdated"] as! Int,
            status: map["status"] as! String,
            runtime: map["runtime"] as! String,
            tag: map["tag"] as! String,
            vars: map["vars"] as! String,
            events: map["events"] as! [Any],
            schedule: map["schedule"] as! String,
            scheduleNext: map["scheduleNext"] as! Int,
            schedulePrevious: map["schedulePrevious"] as! Int,
            timeout: map["timeout"] as! Int
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "$id": id as Any,
            "$permissions": permissions.toMap() as Any,
            "name": name as Any,
            "dateCreated": dateCreated as Any,
            "dateUpdated": dateUpdated as Any,
            "status": status as Any,
            "runtime": runtime as Any,
            "tag": tag as Any,
            "vars": vars as Any,
            "events": events as Any,
            "schedule": schedule as Any,
            "scheduleNext": scheduleNext as Any,
            "schedulePrevious": schedulePrevious as Any,
            "timeout": timeout as Any
        ]
    }
                                                                                                                                                                                                                    
}