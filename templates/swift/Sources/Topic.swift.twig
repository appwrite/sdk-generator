import Foundation

/// Thrown by `Topic.path(_:)` when a level is invalid.
public struct TopicError: Swift.Error, LocalizedError, CustomStringConvertible {
    /// What is wrong with the levels.
    public let message: String

    init(_ message: String) {
        self.message = message
    }

    public var description: String {
        return message
    }

    public var errorDescription: String? {
        return message
    }
}

/// Validate the levels passed to `path(_:)`: a non-empty list of non-empty strings, none holding
/// the level separator `/` or the wildcards `+` and `#` (use `any()` and `all()` for those).
private func levelsOf(_ levels: [String]) throws -> [String] {
    if levels.isEmpty {
        throw TopicError("path() needs at least one level")
    }
    for (index, level) in levels.enumerated() {
        if level.isEmpty {
            throw TopicError("empty level at index \(index)")
        }
        if level.contains("/") {
            let parts =
                level
                .components(separatedBy: "/")
                .map { "'\($0)'" }
                .joined(separator: ", ")
            throw TopicError("\"\(level)\" contains \"/\", split it: path([\(parts)])")
        }
        if level == "+" {
            throw TopicError("\"+\" is reserved, use any()")
        }
        if level.contains("+") {
            throw TopicError("\"\(level)\" contains \"+\", use any() for wildcards")
        }
        if level == "#" {
            throw TopicError("\"#\" is reserved, use all()")
        }
        if level.contains("#") {
            throw TopicError("\"\(level)\" contains \"#\", use all() for wildcards")
        }
    }
    return levels
}

/// Helper to build MQTT topics for `Push`, level by level.
///
///     try Topic.path(["user", userId, "notification"])  // "user/123/notification"
///     try Topic.path(["user"]).any().path(["notification"])  // "user/+/notification"
///     try Topic.path(["org"]).any().path(["logs"]).all()  // "org/+/logs/#"
///
/// `any()` matches exactly one level and `all()` everything below, at any depth (including the
/// parent itself). `all()` ends the topic, so it returns a `ResolvedTopic`.
public final class Topic: CustomStringConvertible, Sendable {
    private let levels: [String]

    private init(_ levels: [String]) {
        self.levels = levels
    }

    /// Start a topic with the given levels, e.g. `try Topic.path(["user", userId])`.
    public static func path(_ levels: [String]) throws -> Topic {
        return Topic(try levelsOf(levels))
    }

    /// Start a topic with a single-level wildcard (`+`).
    public static func any() -> Topic {
        return Topic(["+"])
    }

    /// The multi-level wildcard alone (`#`): matches every topic.
    public static func all() -> ResolvedTopic {
        return ResolvedTopic(["#"])
    }

    /// Append the given levels.
    public func path(_ levels: [String]) throws -> Topic {
        return Topic(self.levels + (try levelsOf(levels)))
    }

    /// Append a single-level wildcard (`+`): matches exactly one level.
    public func any() -> Topic {
        return Topic(levels + ["+"])
    }

    /// Append the multi-level wildcard (`#`) and end the topic.
    public func all() -> ResolvedTopic {
        return ResolvedTopic(levels + ["#"])
    }

    /// The topic string, levels joined with `/`.
    public func toString() -> String {
        return levels.joined(separator: "/")
    }

    public var description: String {
        return toString()
    }
}

/// A topic ended by `all()`: nothing can be appended after the `#` wildcard.
public final class ResolvedTopic: CustomStringConvertible, Sendable {
    private let levels: [String]

    fileprivate init(_ levels: [String]) {
        self.levels = levels
    }

    /// The topic string, levels joined with `/`.
    public func toString() -> String {
        return levels.joined(separator: "/")
    }

    public var description: String {
        return toString()
    }
}
