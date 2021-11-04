import AsyncHTTPClient
import Foundation
import NIO

#if canImport(SwiftUI)
import SwiftUI
#endif

///
/// Used to authenticate with external OAuth2 providers. Launches browser windows and handles
/// suspension until the user completes the process or otherwise returns to the app.
///
@available(iOS 14.0, macOS 11.0, tvOS 14.0, watchOS 7.0, *)
public class WebAuthComponent {

#if canImport(SwiftUI)
    @Environment(\.openURL)
    private static var openURL
#endif

    private static var callbacks = [String: (Result<String, AppwriteError>) -> Void]()

    ///
    /// Authenticate Session with OAuth2
    ///
    /// Launches a chrome custom tab from the given activity and directs to the given url,
    /// suspending until the user returns to the app, at which point the given [onComplete] callback
    /// will run, passing the callback url from the intent used to launch the [CallbackActivity],
    /// or an [IllegalStateException] in the case the user closed the window or returned to the
    /// app without passing through the [CallbackActivity].
    ///
    /// - Parameters:
    ///   - url:                The url to launch
    ///   - callbackScheme:     The callback url scheme used to key the given callback
    ///   - onComplete:         The callback to run when a result (success or failure) is received
    ///
    internal static func authenticate(
        url: URL,
        callbackScheme: String,
        onComplete: @escaping (Result<String, AppwriteError>) -> Void
    ) {
        callbacks[callbackScheme] = onComplete

    #if canImport(SwiftUI)
        openURL(url)
    #endif
    }

    ///
    /// Handle an incoming cooke from a URL, saving it to use for future requests.
    ///
    /// - Parameters:
    ///   - url: The URL containing the cookie
    ///
    public static func handleIncomingCookie(from url: URL) {
        let components = URLComponents(string: url.absoluteString)!

        let cookieParts = [String: String](uniqueKeysWithValues: components.queryItems!.map {
            ($0.name, $0.value!)
        })

        var domain = cookieParts["domain"]!
        domain.remove(at: domain.startIndex)

        let cookie = HTTPClient.Cookie(
            name: cookieParts["key"]!,
            value: cookieParts["secret"]!
        )
        let cookieJson = try! cookie.toJson()
        UserDefaults.standard.set(cookieJson, forKey: "\(domain)-cookies")

        WebAuthComponent.onCallback(
            scheme: components.scheme!,
            url: components.url!
        )
    }

    ///
    /// Trigger a web auth callback
    ///
    /// Attempts to find a callback for the given [scheme] and if found, invokes it, passing the
    /// given [url]. Calling this method stops auth suspension, so any calls to [authenticate]
    /// will continue execution from their suspension points immediately after this method
    /// is called.
    ///
    /// - Parameters:
    ///     - scheme:   The scheme to match to a callback's key
    ///     - url:      The url received through intent data from the [CallbackActivity]
    ///
    public static func onCallback(scheme: String? = nil, url: URL? = nil) {
        if let scheme = scheme, let url = url {
            callbacks.removeValue(forKey: scheme)?(.success(url.absoluteString))
        }
        cleanUp()
    }

    private static func cleanUp() {
        callbacks.forEach { (_, callback) in
            callback(.failure(AppwriteError(message: "User cancelled login.")))
        }
    }
}
