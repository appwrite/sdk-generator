import Foundation
import NIO
import NIOHTTP1
import NIOWebSocket
import Dispatch
import NIOFoundationCompat
import NIOSSL

public let WEBSOCKET_LOCKER_QUEUE = "SyncLocker"
public let WEBSOCKET_THREAD_QUEUE = "ThreadLocker"
public let WEBSOCKET_CHANNEL_QUEUE = "ChannelLocker"

/// Creates and manages connections to a WebSocket server.
///
/// Creates a connection to the remote host and allows setting callbacks for messages sent from the WebSocket server.
public class WebSocketClient {

    // MARK: - Properties
    let host: String
    let port: Int
    let uri: String
    let query: String
    let headers: HTTPHeaders
    let frameKey: String

    public private(set) var maxFrameSize: Int

    var tlsEnabled: Bool = false
    var closeSent: Bool = false

    private let locker = DispatchQueue(label: WEBSOCKET_LOCKER_QUEUE, qos: .background)
    private let channelQueue = DispatchQueue(label: WEBSOCKET_CHANNEL_QUEUE)
    private let threadGroupQueue = DispatchQueue(label: WEBSOCKET_THREAD_QUEUE)

    var channel: Channel? {
        get {
            return channelQueue.sync { _channel }
        }
        set {
            channelQueue.sync { _channel = newValue }
        }
    }
    private var _channel: Channel? = nil

    var threadGroup: MultiThreadedEventLoopGroup? {
        get {
            return threadGroupQueue.sync { _threadGroup }
        }
        set {
            threadGroupQueue.sync { _threadGroup = newValue }
        }
    }
    private var _threadGroup: MultiThreadedEventLoopGroup?

    weak var delegate: WebSocketClientDelegate? = nil

    /// Is this client currently connected to a WebSocket
    public var isConnected: Bool {
        channel?.isActive ?? false
    }
    
    // MARK: - Stored callbacks
    
    private var _openCallback: (Channel) -> Void = { _ in }
    var onOpen: (Channel) -> Void {
        get {
            return locker.sync {
                return _openCallback
            }
        }
        set {
            locker.sync {
                _openCallback = newValue
            }
        }
    }
    
    private var _closeCallback: (Channel, Data) -> Void = { _,_ in }
    var onClose: (Channel, Data) -> Void {
        get {
            return locker.sync {
                return _closeCallback
            }
        }
        set {
            locker.sync {
                _closeCallback = newValue
            }
        }
    }

    private var _textCallback: (String) -> Void = { _ in }
    var onTextMessage: (String) -> Void {
        get {
            return locker.sync {
                return _textCallback
            }
        }
        set {
            locker.sync {
                _textCallback = newValue
            }
        }
    }
    
    private var _binaryCallback: (Data) -> Void = { _ in }
    var onBinaryMessage: (Data) -> Void {
        get {
            return locker.sync {
                return _binaryCallback
            }
        }
        set {
            locker.sync {
                _binaryCallback = newValue
            }
        }
    }
    
    private var _errorCallBack: (Swift.Error?, HTTPResponseStatus?) -> Void = { _,_ in }
    var onError: (Swift.Error?, HTTPResponseStatus?) -> Void {
        get {
            return locker.sync {
                return _errorCallBack
            }
        }
        set {
            locker.sync {
                _errorCallBack = newValue
            }
        }
    }
    
    // MARK: - Callback setters
    
    /// Set a callback to be fired when a WebSocket connection is opened.
    ///
    /// - parameters:
    ///     - callback: Callback to fie when a WebSocket connection is opened
    public func onOpen(_ callback: @escaping (Channel) -> Void) {
        onOpen = callback
    }
    
    /// Set a callback to be fired when a WebSocket text message is received.
    ///
    /// - parameters:
    ///     - callback: Callback to fie when a WebSocket text message is received
    public func onMessage(_ callback: @escaping (String) -> Void) {
        onTextMessage = callback
    }

    /// Set a callback to be fired when a WebSocket binary message is received.
    ///
    /// - parameters:
    ///     - callback: Callback to fie when a WebSocket binary message is received
    public func onMessage(_ callback: @escaping (Data) -> Void) {
        onBinaryMessage = callback
    }

    /// Set a callback to be fired when a WebSocket close message is received.
    ///
    /// - parameters:
    ///     - callback: Callback to fie when a WebSocket close message is received
    public func onClose(_ callback: @escaping (Channel, Data) -> Void) {
        onClose = callback
    }

    /// Set a callback to be fired when a WebSocket error occurs.
    ///
    /// - parameters:
    ///     - callback: Callback to fie when a WebSocket error occurs
    public func onError(_ callback: @escaping (Swift.Error?, HTTPResponseStatus?) -> Void) {
        onError = callback
    }
    
    // MARK: - Constructors
    
    /// Create a new `WebSocketClient`.
    ///
    /// - parameters:
    ///     - host:         Host name of the remote server
    ///     - port:         Port number on which the remote server is listening
    ///     - uri:          The "Request-URI" of the GET method, it is used to identify the endpoint of the WebSocket connection
    ///     - frameKey:     The key sent by client which server has to include while building it's response.
    ///     - maxFrameSize: Maximum allowable frame size of WebSocket client is configured using this parameter.
    ///     - tlsEnabled:   Is TLS enabled for this client.
    ///     - delegate:     Delegate to handle message and error callbacks
    public init?(
        host: String,
        port: Int,
        uri: String,
        query: String,
        frameKey: String,
        headers: HTTPHeaders = HTTPHeaders(),
        maxFrameSize: Int = 14,
        tlsEnabled: Bool = true,
        delegate: WebSocketClientDelegate? = nil
    ) {
        self.host = host
        self.port = port
        self.uri = uri
        self.query = query
        self.headers = headers
        self.frameKey = frameKey
        self.maxFrameSize = maxFrameSize
        self.tlsEnabled = tlsEnabled
        self.delegate = delegate

        DispatchQueue.global(qos: .background).async {
            self.threadGroup = MultiThreadedEventLoopGroup(numberOfThreads: 1)
        }
    }

    /// Create a new `WebSocketClient`.
    ///
    /// - parameters:
    ///     - url:          The absolute URL of the GET method used to identify the WebSocket endpoint
    ///     - delegate:     Delegate to handle message and error callbacks.
    public init?(
        _ url: String,
        tlsEnabled: Bool = true,
        headers: HTTPHeaders = HTTPHeaders(),
        delegate: WebSocketClientDelegate? = nil
    ) {
        let rawUrl = URL(string: url)
        self.frameKey = "tergregfgbsfdgfdsfgdbv=="
        self.host = rawUrl?.host ?? "localhost"
        self.port = rawUrl?.port ?? (tlsEnabled ? 443 : 80)
        self.uri = rawUrl?.path ?? "/"
        self.query = rawUrl?.query ?? ""
        self.headers = headers
        self.maxFrameSize = 24
        self.tlsEnabled = tlsEnabled
        self.delegate = delegate

        DispatchQueue.global(qos: .background).async {
            self.threadGroup = MultiThreadedEventLoopGroup(numberOfThreads: 1)
        }
    }

    // MARK: - Open connection

    /// Open a connection to the configured host and attempt to upgrade the connection to a WebSocket. If successful the `onOpen` callback will fire, otherwise a connection error will be thrown from here.
    public func connect() async throws {
        let socketOptions = ChannelOptions.socket(
            SocketOptionLevel(SOL_SOCKET),
            SO_REUSEPORT
        )

        while(threadGroup == nil) {
            try? await Task.sleep(nanoseconds: 10_000_000)
        }

        let bootstrap = ClientBootstrap(group: threadGroup!)
            .channelOption(socketOptions, value: 1)
            .channelInitializer {
                self.openChannel(channel: $0)
            }

        _ = try await bootstrap
            .connect(host: self.host,port: self.port)
            .get()
    }

    private func openChannel(channel: Channel) -> EventLoopFuture<Void> {
        let httpHandler = HTTPHandler(client: self, headers: headers)

        let basicUpgrader = NIOWebSocketClientUpgrader(
            requestKey: self.frameKey,
            upgradePipelineHandler: { channel, response in
                self.upgradePipelineHandler(channel: channel, response: response)
            }
        )

        let config: NIOHTTPClientUpgradeConfiguration = (upgraders: [basicUpgrader], completionHandler: { context in
            context.channel.pipeline.removeHandler(httpHandler, promise: nil)
        })

        return channel.pipeline.addHTTPClientHandlers(withClientUpgrade: config).flatMap { _ in
            return channel.pipeline.addHandler(httpHandler).flatMap { _ in
                if self.tlsEnabled {
                    let tlsConfig = TLSConfiguration.makeClientConfiguration()
                    let sslContext = try! NIOSSLContext(configuration: tlsConfig)
                    let sslHandler = try! NIOSSLClientHandler(context: sslContext, serverHostname: self.host)
                    return channel.pipeline.addHandler(sslHandler, position: .first)
                } else {
                    return channel.eventLoop.makeSucceededFuture(())
                }
            }
        }
    }

    private func upgradePipelineHandler(channel: Channel, response: HTTPResponseHead) -> EventLoopFuture<Void> {
        let handler = MessageHandler(client: self)

        if response.status == .switchingProtocols {
            self.channel = channel
        }

        return channel.pipeline.addHandler(handler)
    }

    // MARK: - Close connection

    /// Closes the connection
    ///
    /// - parameters:
    ///     - data: Close frame payload
    public func close(
        data: Data = Data(),
        promise: EventLoopPromise<Void>? = nil
    ) {
        closeSent = true

        var buffer = ByteBufferAllocator()
            .buffer(capacity: data.count)

        buffer.writeBytes(data)

        send(
            data: buffer,
            opcode: .connectionClose,
            finalFrame: true,
            promise: promise
        )
    }

    // MARK: - Send data

    /// Sends binary-formatted data  to the connected server in multiple frames.
    ///
    /// - parameters:
    ///     - raw:          Raw data to be sent in the frame
    ///     - opcode:       Websocket opcode indicating type of the frame
    ///     - finalFrame:   Whether the frame to be sent is the last one
    public func send(
        data: Data,
        opcode: WebSocketOpcode,
        finalFrame: Bool = true,
        promise: EventLoopPromise<Void>? = nil
    ) {
        var buffer = ByteBufferAllocator()
            .buffer(capacity: data.count)

        buffer.writeBytes(data)

        if opcode == .connectionClose {
            self.closeSent = true
        }

        send(
            data: buffer,
            opcode: opcode,
            finalFrame: finalFrame,
            promise: promise
        )
    }

    /// Sends text-formatted data to the connected server in multiple frames.
    ///
    /// - parameters:
    ///     - raw:          Raw text to be sent in the frame
    ///     - opcode:       Websocket opcode indicating type of the frame
    ///     - finalFrame:   Whether the frame to be sent is the last one
    public func send(
        text: String,
        opcode: WebSocketOpcode = .text,
        finalFrame: Bool = true,
        promise: EventLoopPromise<Void>? = nil
    ) {
        var buffer = ByteBufferAllocator()
            .buffer(capacity: text.count)

        buffer.writeString(text)

        send(
            data: buffer,
            opcode: opcode,
            finalFrame: finalFrame,
            promise: promise
        )
    }


    /// Sends the JSON representation of the given model to the connected server in multiple frames.
    ///
    /// - parameters:
    ///     - model:        The model to encode and send
    ///     - opcode:       Websocket opcode indicating type of the frame
    ///     - finalFrame:   Whether the frame to be sent is the last one
    public func send<T: Codable>(
        model: T,
        opcode: WebSocketOpcode = .text,
        finalFrame: Bool = true,
        promise: EventLoopPromise<Void>? = nil
    ) {
        let jsonEncoder = JSONEncoder()
        do {
            let jsonData = try jsonEncoder.encode(model)
            let string = String(data: jsonData, encoding: .utf8)!
            var buffer = ByteBufferAllocator()
                .buffer(capacity: string.count)

            buffer.writeString(string)

            send(
                data: buffer,
                opcode: opcode,
                finalFrame: finalFrame,
                promise: promise
            )
        } catch let error {
            print(error)
        }
    }

    /// Sends buffered bytes to the connected server in multiple frames.
    ///
    /// - parameters:
    ///     - model:        The model to encode and send
    ///     - opcode:       Websocket opcode indicating type of the frame
    ///     - finalFrame:   Whether the frame to be sent is the last one
    public func send(
        data: ByteBuffer,
        opcode: WebSocketOpcode,
        finalFrame: Bool,
        promise: EventLoopPromise<Void>? = nil
    ) {
        let frame = WebSocketFrame(
            fin: finalFrame,
            opcode: opcode,
            maskKey: nil,
            data: data
        )

        guard let channel = channel else {
            return
        }

        if finalFrame {
            channel.writeAndFlush(frame, promise: promise)
        } else {
            channel.write(frame, promise: promise)
        }

        if opcode == .connectionClose {
            channel.close(mode: .all, promise: promise)
        }
    }
}
