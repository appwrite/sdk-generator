import Foundation

public class RealtimeSubscription {
    private var close: () async throws -> Void

    init(close: @escaping () async throws-> Void) {
        self.close = close
    }

    public func close() async throws {
        try await self.close()
    }
}

public class RealtimeCallback {
    public let channels: Set<String>
    public let callback: (RealtimeResponseEvent) -> Void

    init(
        for channels: Set<String>,
        with callback: @escaping (RealtimeResponseEvent) -> Void
    ) {
        self.channels = channels
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
