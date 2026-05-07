import Foundation

public class RealtimeSubscriptionUpdate {
    public let channels: [ChannelValue]?
    public let queries: [String]?

    public init(channels: [ChannelValue]? = nil, queries: [String]? = nil) {
        self.channels = channels
        self.queries = queries
    }
}

public class RealtimeSubscription {
    private var unsubscribeAction: () async throws -> Void
    private var updateAction: (RealtimeSubscriptionUpdate) async throws -> Void
    private var closeAction: () async throws -> Void

    init(
        unsubscribe: @escaping () async throws -> Void,
        update: @escaping (RealtimeSubscriptionUpdate) async throws -> Void,
        close: @escaping () async throws -> Void
    ) {
        self.unsubscribeAction = unsubscribe
        self.updateAction = update
        self.closeAction = close
    }

    /// Remove this subscription only. The WebSocket stays open so other subscriptions keep
    /// receiving events; use `Realtime.disconnect()` to shut the connection down.
    public func unsubscribe() async throws {
        try await self.unsubscribeAction()
    }

    /// Replace the channels and/or queries on this subscription without recreating it.
    public func update(_ changes: RealtimeSubscriptionUpdate) async throws {
        try await self.updateAction(changes)
    }

    /// Alias of `unsubscribe()` that also tears the socket down when this was the last active
    /// subscription. Prefer `unsubscribe()` plus `Realtime.disconnect()` for explicit control.
    public func close() async throws {
        try await self.closeAction()
    }
}

public class RealtimeCallback {
    public var channels: Set<String>
    public var queries: [String]
    public let callback: (RealtimeResponseEvent) -> Void

    init(
        for channels: Set<String>,
        queries: [String],
        with callback: @escaping (RealtimeResponseEvent) -> Void
    ) {
        self.channels = channels
        self.queries = queries
        self.callback = callback
    }
}

public class RealtimeResponseEvent {
    public let events: [String]?
    public let channels: [String]?
    public let timestamp: String?
    public var payload: [String: Any]?

    init(
        events: [String],
        channels: [String],
        timestamp: String,
        payload: [String: Any]
    ) {
        self.events = events
        self.channels = channels
        self.timestamp = timestamp
        self.payload = payload
    }
}
