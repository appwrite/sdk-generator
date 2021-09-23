//
//  Created by Jake Barnby on 13/09/21.
//

import Foundation

public class RealtimeSubscription {
    public var close: () -> Void

    init(close: @escaping () -> Void) {
        self.close = close
    }
}

public class RealtimeCallback<T : AnyObject & Decodable> {
    public let payloadType: T.Type
    public let callback: (RealtimeResponseEvent<T>) -> Void

    init(
        with payloadType: T.Type,
        and callback: @escaping (RealtimeResponseEvent<T>) -> Void
    ) {
        self.payloadType = payloadType
        self.callback = callback
    }
}

public class RealtimeResponse : Decodable {
    public let type: String
    public var data: Model

    init(
        of type: String,
        with data: Model
    ) {
        self.type = type
        self.data = data
    }
}

public class RealtimeResponseEvent<T : AnyObject & Decodable> : Decodable {
    public let event: String
    public let channels: Array<String>
    public let timestamp: Int64
    public var payload: T

    init(
        event: String,
        channels: Array<String>,
        timestamp: Int64,
        payload: T
    ) {
        self.event = event
        self.channels = channels
        self.timestamp = timestamp
        self.payload = payload
    }
}

enum RealtimeCode {
    static let policyViolation = 1008
    static let unknown = -1
}

open class Model : Codable {}
