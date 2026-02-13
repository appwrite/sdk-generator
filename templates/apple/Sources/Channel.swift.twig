import Foundation

// Marker structs for type safety (structs work with == constraints in extensions)
public struct _Root {}
public struct _Database {}
public struct _Collection {}
public struct _Document {}
public struct _TablesDB {}
public struct _Table {}
public struct _Row {}
public struct _Bucket {}
public struct _File {}
public struct _Func {}
public struct _Execution {}
public struct _Team {}
public struct _Membership {}
public struct _Resolved {}

// Helper for normalizing IDs
private func normalize(_ id: String) -> String {
    let trimmed = id.trimmingCharacters(in: .whitespacesAndNewlines)
    return trimmed.isEmpty ? "*" : trimmed
}

/// Channel class with generic type parameter for type-safe method chaining
public class RealtimeChannel<T> {
    private let segments: [String]
    
    internal init(_ segments: [String]) {
        self.segments = segments
    }
    
    /// Internal helper to transition to next state with segment and optional ID
    internal func next<N>(_ segment: String, _ id: String? = nil) -> RealtimeChannel<N> {
        if let id = id {
            return RealtimeChannel<N>(segments + [segment, normalize(id)])
        }

        return RealtimeChannel<N>(segments + [segment])
    }
    
    /// Internal helper for terminal actions (no ID segment)
    internal func resolve(_ action: String) -> RealtimeChannel<_Resolved> {
        return RealtimeChannel<_Resolved>(segments + [action])
    }
    
    /// Convert channel to string representation
    public func toString() -> String {
        return segments.joined(separator: ".")
    }
}

/// Non-generic Channel enum for static factory methods
public enum Channel {
    
    // MARK: - Root Factories
    
    public static func database(_ id: String = "*") -> RealtimeChannel<_Database> {
        return RealtimeChannel<_Database>(["databases", normalize(id)])
    }
    
    public static func tablesdb(_ id: String = "*") -> RealtimeChannel<_TablesDB> {
        return RealtimeChannel<_TablesDB>(["tablesdb", normalize(id)])
    }
    
    public static func bucket(_ id: String = "*") -> RealtimeChannel<_Bucket> {
        return RealtimeChannel<_Bucket>(["buckets", normalize(id)])
    }
    
    public static func function(_ id: String = "*") -> RealtimeChannel<_Func> {
        return RealtimeChannel<_Func>(["functions", normalize(id)])
    }
    
    public static func execution(_ id: String = "*") -> RealtimeChannel<_Execution> {
        return RealtimeChannel<_Execution>(["executions", normalize(id)])
    }
    
    public static func team(_ id: String = "*") -> RealtimeChannel<_Team> {
        return RealtimeChannel<_Team>(["teams", normalize(id)])
    }
    
    public static func membership(_ id: String = "*") -> RealtimeChannel<_Membership> {
        return RealtimeChannel<_Membership>(["memberships", normalize(id)])
    }
    
    public static func account() -> String {
        return "account"
    }
    
    // Global events
    public static func documents() -> String { "documents" }
    public static func rows() -> String { "rows" }
    public static func files() -> String { "files" }
    public static func executions() -> String { "executions" }
    public static func teams() -> String { "teams" }
    public static func memberships() -> String { "memberships" }
}

// MARK: - DATABASE ROUTE
// Protocol extensions restricted by receiver type

/// Only available on RealtimeChannel<_Database>
extension RealtimeChannel where T == _Database {
    public func collection(_ id: String? = nil) -> RealtimeChannel<_Collection> {
        return self.next("collections", id ?? "*")
    }
}

/// Only available on RealtimeChannel<_Collection>
extension RealtimeChannel where T == _Collection {
    public func document(_ id: String? = nil) -> RealtimeChannel<_Document> {
        return self.next("documents", id)
    }
}

// MARK: - TABLESDB ROUTE

/// Only available on RealtimeChannel<_TablesDB>
extension RealtimeChannel where T == _TablesDB {
    public func table(_ id: String? = nil) -> RealtimeChannel<_Table> {
        return self.next("tables", id ?? "*")
    }
}

/// Only available on RealtimeChannel<_Table>
extension RealtimeChannel where T == _Table {
    public func row(_ id: String? = nil) -> RealtimeChannel<_Row> {
        return self.next("rows", id)
    }
}

// MARK: - BUCKET ROUTE

/// Only available on RealtimeChannel<_Bucket>
extension RealtimeChannel where T == _Bucket {
    public func file(_ id: String? = nil) -> RealtimeChannel<_File> {
        return self.next("files", id)
    }
}

// MARK: - TERMINAL ACTIONS
// Restricted to actionable types (_Document, _Row, _File, _Execution, _Team, _Membership)

/// Only available on RealtimeChannel<_Document>
extension RealtimeChannel where T == _Document {
    public func create() -> RealtimeChannel<_Resolved> {
        return self.resolve("create")
    }
    
    public func update() -> RealtimeChannel<_Resolved> {
        return self.resolve("update")
    }
    
    public func delete() -> RealtimeChannel<_Resolved> {
        return self.resolve("delete")
    }
}

/// Only available on RealtimeChannel<_Row>
extension RealtimeChannel where T == _Row {
    public func create() -> RealtimeChannel<_Resolved> {
        return self.resolve("create")
    }
    
    public func update() -> RealtimeChannel<_Resolved> {
        return self.resolve("update")
    }
    
    public func delete() -> RealtimeChannel<_Resolved> {
        return self.resolve("delete")
    }
}

/// Only available on RealtimeChannel<_File>
extension RealtimeChannel where T == _File {
    public func create() -> RealtimeChannel<_Resolved> {
        return self.resolve("create")
    }
    
    public func update() -> RealtimeChannel<_Resolved> {
        return self.resolve("update")
    }
    
    public func delete() -> RealtimeChannel<_Resolved> {
        return self.resolve("delete")
    }
}

/// Only available on RealtimeChannel<_Team>
extension RealtimeChannel where T == _Team {
    public func create() -> RealtimeChannel<_Resolved> {
        return self.resolve("create")
    }
    
    public func update() -> RealtimeChannel<_Resolved> {
        return self.resolve("update")
    }
    
    public func delete() -> RealtimeChannel<_Resolved> {
        return self.resolve("delete")
    }
}

/// Only available on RealtimeChannel<_Membership>
extension RealtimeChannel where T == _Membership {
    public func create() -> RealtimeChannel<_Resolved> {
        return self.resolve("create")
    }
    
    public func update() -> RealtimeChannel<_Resolved> {
        return self.resolve("update")
    }
    
    public func delete() -> RealtimeChannel<_Resolved> {
        return self.resolve("delete")
    }
}

// MARK: - Protocol for backward compatibility

/// Protocol for channel values that can be converted to strings
public protocol ChannelValue {
    func toString() -> String
}

// Make String conform to ChannelValue
extension String: ChannelValue {
    public func toString() -> String {
        return self
    }
}

// Make RealtimeChannel conform to ChannelValue
extension RealtimeChannel: ChannelValue {}
