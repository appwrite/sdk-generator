import Foundation
import NIO
import NIOHTTP1
import NIOWebSocket

/// Handles the HTTP pipeline for opening a WebSocket connection.
///
/// Adds the required headers to the outbound upgrade connection request and handles success and failures responses.
class MessageHandler {

    private let client: WebSocketClient
    private var buffer: ByteBuffer
    private var binaryBuffer: Data = Data()
    private var isText: Bool = false
    private var string: String = ""

    public init(client: WebSocketClient) {
        self.client = client
        self.buffer = ByteBufferAllocator().buffer(capacity: 0)
    }
    
    private func unmaskedData(frame: WebSocketFrame) -> ByteBuffer {
        var frameData = frame.data
        if let maskingKey = frame.maskKey {
            frameData.webSocketUnmask(maskingKey)
        }
        return frameData
    }
}

extension MessageHandler: ChannelInboundHandler, RemovableChannelHandler {

    typealias InboundIn = WebSocketFrame
    
    public func channelRead(context: ChannelHandlerContext, data: NIOAny) {
        let frame = self.unwrapInboundIn(data)
        switch frame.opcode {
        case .text:
            let data = unmaskedData(frame: frame)
            if frame.fin {
                guard let text = data.getString(at: 0, length: data.readableBytes) else {
                    return
                }
                if let delegate = client.delegate {
                    try! delegate.onMessage(text: text)
                } else {
                    client.onTextMessage(text)
                }
            } else {
                isText = true
                guard let text = data.getString(at: 0, length: data.readableBytes) else {
                    return
                }
                string = text
            }
        case .binary:
            let data = unmaskedData(frame: frame)
            if frame.fin {
                guard let binaryData = data.getData(at: 0, length: data.readableBytes) else {
                    return
                }
                if let delegate = client.delegate {
                    try! delegate.onMessage(data: binaryData)
                } else {
                    client.onBinaryMessage(binaryData)
                }
            } else {
                guard let binaryData = data.getData(at: 0, length: data.readableBytes) else {
                    return
                }
                binaryBuffer = binaryData
            }
        case .continuation:
            let data = unmaskedData(frame: frame)
            if isText {
                if frame.fin {
                    guard let text = data.getString(at: 0, length: data.readableBytes) else {
                        return
                    }
                    string.append(text)
                    isText = false
                    if let delegate = client.delegate {
                        try! delegate.onMessage(text: string)
                    } else {
                        client.onTextMessage(string)
                    }
                } else {
                    guard let text = data.getString(at: 0, length: data.readableBytes) else {
                        return
                    }
                    string.append(text)
                }
            } else {
                if frame.fin {
                    guard let binaryData = data.getData(at: 0, length: data.readableBytes) else {
                        return
                    }
                    binaryBuffer.append(binaryData)
                    if let delegate = client.delegate {
                        try! delegate.onMessage(data: binaryBuffer)
                    } else {
                        client.onBinaryMessage(binaryBuffer)
                    }
                } else {
                    guard let binaryData = data.getData(at: 0, length: data.readableBytes) else {
                        return
                    }
                    binaryBuffer.append(binaryData)
                }
            }
        case .connectionClose:
            guard frame.fin else {
                return
            }
            let data = frame.data
            if !client.closeSent {
                client.close(data: frame.data.getData(at: 0, length: frame.data.readableBytes) ?? Data())
            }
            if let delegate = client.delegate {
                delegate.onClose(channel: context.channel, data: data.getData(at: 0, length: data.readableBytes)!)
            } else {
                client.onClose(context.channel, data.getData(at: 0, length: data.readableBytes)!)
            }
        default:
            break
        }
    }
    
    public func errorCaught(context: ChannelHandlerContext, error: Swift.Error) {
        if client.delegate != nil {
            try! client.delegate?.onError(error: error, status: nil)
        } else {
            client.onError(error, nil)
        }
        client.close()
    }
}
