import Foundation
import NIO
import NIOHTTP1

/// Handles the HTTP pipeline for opening a WebSocket connection.
///
/// Adds the required headers to the outbound upgrade connection request and handles success and failures responses.
class HTTPHandler {

    unowned let client: WebSocketClient

    let headers: HTTPHeaders

    let upgraded: EventLoopPromise<Void>

    init(client: WebSocketClient, headers: HTTPHeaders, upgraded: EventLoopPromise<Void>) {
        self.client = client
        self.headers = headers
        self.upgraded = upgraded
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

extension HTTPHandler: ChannelInboundHandler, RemovableChannelHandler {

    public typealias InboundIn = HTTPClientResponsePart
    public typealias OutboundOut = HTTPClientRequestPart

    func channelActive(context: ChannelHandlerContext) {
        var headers = HTTPHeaders()

        headers.add(name: "Host", value: "\(client.host):\(client.port)")
        headers.add(contentsOf: self.headers)
        headers.addDomainCookies(for: client.host)
        let requestHead = HTTPRequestHead(
            version: .http1_1,
            method: .GET,
            uri: "\(client.uri)?\(client.query)",
            headers: headers
        )

        context.write(wrapOutboundOut(.head(requestHead)), promise: nil)
        context.writeAndFlush(wrapOutboundOut(.end(nil)), promise: nil)
    }

    func channelRead(context: ChannelHandlerContext, data: NIOAny) {
        let response = unwrapInboundIn(data)

        switch response {
        case .head(let header):
            FileHandle.standardError.write(Data("\(String(describing: response))\n".utf8))
            upgradeFailure(status: header.status)
            upgraded.fail(WebSocketClientError(rawValue: UInt(header.status.code)) ?? .upgradeRejected)
        case .body(var body):
            FileHandle.standardError.write(Data("\(body.readString(length: body.readableBytes) ?? "")\n".utf8))
        case .end(_):
            break
        }
    }

    func channelInactive(context: ChannelHandlerContext) {
        upgraded.fail(ChannelError.ioOnClosedChannel)
        context.fireChannelInactive()
    }

    func errorCaught(context: ChannelHandlerContext, error: Swift.Error) {
        upgraded.fail(error)
        if client.delegate != nil {
            try! client.delegate?.onError(error: error, status: nil)
        } else {
            client.onError(error, nil)
        }
    }
}
