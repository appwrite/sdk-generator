#if os(macOS)
typealias OSApplication = NSApplication
typealias OSViewController = NSViewController
let notificationType = NSApplication.willBecomeActiveNotification
#elseif os(iOS) || os(tvOS) || os(visionOS)
typealias OSApplication = UIApplication
typealias OSViewController = UIViewController
let notificationType = UIApplication.willEnterForegroundNotification
#elseif os(watchOS)
typealias OSApplication = WKApplication
typealias OSViewController = WKInterfaceController
let notificationType = WKApplication.willEnterForegroundNotification
#endif

#if canImport(SwiftUI)
import SwiftUI
@available(iOS 14.0, macOS 11.0, tvOS 14.0, watchOS 7.0, visionOS 1.0, *)
extension View {
    public func registerOAuthHandler() -> some View {
        onOpenURL { url in
            WebAuthComponent.handleIncomingCookie(from: url)
        }.onReceive(NotificationCenter.default.publisher(for: notificationType)) { _ in
            WebAuthComponent.onCallback()
        }
    }
}
#endif

#if canImport(OSViewController)
@available(iOS 14.0, macOS 11.0, tvOS 14.0, watchOS 7.0, visionOS 1.0, *)
extension OSViewController {
    public func registerOAuthHandler() {
        #if os(macOS)
        typealias OSHostingController = NSHostingController
        #elseif os(iOS) || os(tvOS) || os(watchOS) || os(visionOS)
        typealias OSHostingController = UIHostingController
        #endif
        self.addChild(OSHostingController(rootView: EmptyView().registerOAuthHandler()))
    }
}
#endif
