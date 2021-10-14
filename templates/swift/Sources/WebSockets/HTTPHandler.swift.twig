import NIO
import NIOHTTP1
import Foundation

/// Handles the HTTP pipeline for opening a WebSocket connection.
///
/// Adds the required headers to the outbound upgrade connection request and handles success and failures responses.
class HTTPHandler {

    unowned let client: WebSocketClient
    
    let headers: HTTPHeaders

    init(client: WebSocketClient, headers: HTTPHeaders) {
        self.client = client
        self.headers = headers
    }

    func upgradeFailure(status: HTTPResponseStatus) {
        if let delegate = client.delegate {
            switch status {
            case .badRequest:
                try! delegate.onError(error: WebSocketClientError.badRequest, status: status)
            case .notFound:
                try! delegate.onError(error: WebSocketClientError.notFound, status: status)
            default:
                break
            }
        } else {
            switch status {
            case .badRequest:
                client.onError(WebSocketClientError.badRequest, status)
            case .notFound:
                client.onError(WebSocketClientError.notFound, status)
            default:
                break
            }
        }
    }
}

extension HTTPHandler : ChannelInboundHandler, RemovableChannelHandler {
    
    public typealias InboundIn = HTTPClientResponsePart
    public typealias OutboundOut = HTTPClientRequestPart
    
    func channelActive(context: ChannelHandlerContext) {
        var headers = HTTPHeaders()
        
        headers.add(name: "Host", value: "\(client.host):\(client.port)")
        headers.add(name: "Content-Type", value: "text/plain")
        headers.add(name: "Content-Length", value: "\(1)")
        headers.add(contentsOf: self.headers)
        let requestHead = HTTPRequestHead(
            version: .http1_1,
            method: .GET,
            uri: "\(client.uri)?\(client.query)",
            headers: headers
        )
        
        context.write(wrapOutboundOut(.head(requestHead)), promise: nil)
        context.write(wrapOutboundOut(.body(.byteBuffer(ByteBuffer(string: "\r\n")))), promise: nil)
        context.writeAndFlush(wrapOutboundOut(.end(nil)), promise: nil)
    }

    func channelRead(context: ChannelHandlerContext, data: NIOAny) {
        let response = unwrapInboundIn(data)

        switch response {
        case .head(let header):
            print(String(describing: response))
            upgradeFailure(status: header.status)
            break
        case .body(var body):
            print(body.readString(length: body.readableBytes)!)
            break
        case .end(_):
            break
        }
    }

    func errorCaught(context: ChannelHandlerContext, error: Swift.Error) {
        if client.delegate != nil {
            try! client.delegate?.onError(error: error, status: nil)
        } else {
            client.onError(error, nil)
        }
    }
}
