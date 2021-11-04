import Foundation
import AsyncHTTPClient
import NIO
import NIOHTTP1

open class Realtime : Service {

    private let TYPE_ERROR = "error"
    private let TYPE_EVENT = "event"
    private let DEBOUNCE_MILLIS = 1

    private var socketClient: WebSocketClient? = nil
    private var activeChannels = Set<String>()
    private var activeSubscriptions = [Int: RealtimeCallback]()

    let connectSync = DispatchQueue(label: "ConnectSync")
    let callbackSync = DispatchQueue(label: "CallbackSync")

    private var subCallDepth = 0
    private var reconnectAttempts = 0
    private var subscriptionsCounter = 0
    private var reconnect = true

    private func createSocket() {
        guard activeChannels.count > 0 else {
            return
        }

        var queryParams = "project=\(client.config["project"]!)"

        for channel in activeChannels {
            queryParams += "&channels[]=\(channel)"
        }

        let url = "\(client.endPointRealtime!)/realtime?\(queryParams)"

        if (socketClient != nil) {
            reconnect = false
            closeSocket()
        } else {
            socketClient = WebSocketClient(url, delegate: self)!
        }

        try! socketClient?.connect()
    }

    private func closeSocket() {
        socketClient?.close()
        //socket?.close(RealtimeCode.POLICY_VIOLATION.value, null)
    }

    private func getTimeout() -> Int {
        switch reconnectAttempts {
        case 0..<5: return 1000
        case 5..<15: return 5000
        case 15..<100: return 10000
        default: return 60000
        }
    }

    public func subscribe(
        channel: String,
        callback: @escaping (RealtimeResponseEvent) -> Void
    ) -> RealtimeSubscription {
        return subscribe(
            channels: [channel],
            payloadType: String.self,
            callback: callback
        )
    }

    public func subscribe(
        channels: Set<String>,
        callback: @escaping (RealtimeResponseEvent) -> Void
    ) -> RealtimeSubscription {
        return subscribe(
            channels: channels,
            payloadType: String.self,
            callback: callback
        )
    }

    public func subscribe<T : Codable>(
        channel: String,
        payloadType: T.Type,
        callback: @escaping (RealtimeResponseEvent) -> Void
    ) -> RealtimeSubscription {
        return subscribe(
            channels: [channel],
            payloadType: T.self,
            callback: callback
        )
    }

    public func subscribe<T : Codable>(
        channels: Set<String>,
        payloadType: T.Type,
        callback: @escaping (RealtimeResponseEvent) -> Void
    ) -> RealtimeSubscription {
        subscriptionsCounter += 1
        let counter = subscriptionsCounter

        channels.forEach {
            activeChannels.insert($0)
        }

        activeSubscriptions[counter] = RealtimeCallback(
            for: Set(channels),
            and: callback
        )

        connectSync.sync {
            subCallDepth+=1
        }

        DispatchQueue.main.asyncAfter(deadline: .now() + .milliseconds(DEBOUNCE_MILLIS)) {
            if (self.subCallDepth == 1) {
                self.createSocket()
            }
            self.connectSync.sync {
                self.subCallDepth-=1
            }
        }

        return RealtimeSubscription {
            self.activeSubscriptions[counter] = nil
            self.cleanUp(channels: channels)
            self.createSocket()
        }
    }

    func cleanUp(channels: Set<String>) {
        activeChannels = activeChannels.filter { channel in
            guard channels.contains(channel) else {
                return true
            }
            let subsWithChannel = activeSubscriptions.filter { callback in
                return callback.value.channels.contains(channel)
            }
            return subsWithChannel.isEmpty
        }
    }
}

extension Realtime: WebSocketClientDelegate {

    public func onOpen(channel: Channel) {
        self.reconnectAttempts = 0
    }

    public func onMessage(text: String) {
        let data = Data(text.utf8)
        if let json = try! JSONSerialization.jsonObject(with: data, options: []) as? [String: Any] {
            if let type = json["type"] as? String {
                switch type {
                case TYPE_ERROR: try! handleResponseError(from: json)
                case TYPE_EVENT: handleResponseEvent(from: json)
                default: break
                }
            }
        }
    }

    public func onClose(channel: Channel, data: Data) {
        if (!reconnect) {
            reconnect = true
            return
        }

        let timeout = getTimeout()

        print("Realtime disconnected. Re-connecting in \(timeout / 1000) seconds.")

        DispatchQueue.main.asyncAfter(deadline: .now() + .milliseconds(timeout)) {
            self.reconnectAttempts += 1
            self.createSocket()
        }
    }

    public func onError(error: Swift.Error?, status: HTTPResponseStatus?) {
        print(error?.localizedDescription ?? "Unknown error")
    }

    func handleResponseError(from json: [String: Any]) throws {
        throw AppwriteError(message: json["message"] as? String ?? "Unknown error")
    }

    func handleResponseEvent(from json: [String: Any]) {
        guard let data = json["data"] as? [String: Any] else {
            return
        }
        guard let channels = data["channels"] as? Array<String> else {
            return
        }
        guard let payload = data["payload"] as? [String: Any] else {
            return
        }
        guard channels.contains(where: { channel in
            activeChannels.contains(channel)
        }) else {
            return
        }

        for subscription in activeSubscriptions {
            if channels.contains(where: { subscription.value.channels.contains($0) }) {
                let response = RealtimeResponseEvent(
                    event: data["event"] as! String,
                    channels: channels,
                    timestamp: data["timestamp"] as! Int64,
                    payload: payload
                )
                subscription.value.callback(response)
            }
        }
    }
}
