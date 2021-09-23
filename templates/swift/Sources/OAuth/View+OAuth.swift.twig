#if canImport(SwiftUI)
import SwiftUI

@available(iOS 14.0, macOS 11.0, tvOS 14.0, watchOS 7.0, *)
extension View {
    public func registerOAuthHandler() -> some View {
        onOpenURL { url in
            WebAuthComponent.handleIncomingCookie(from: url)
        }.onReceive(NotificationCenter.default.publisher(for: UIApplication.willEnterForegroundNotification)) { _ in
            WebAuthComponent.onCallback()
        }
    }
}
#endif

#if canImport(UIKit)
@available(iOS 14.0, tvOS 14.0, watchOS 7.0, *)
extension UIViewController {
    public func registerOAuthHandler() {
        self.addChild(UIHostingController(rootView: EmptyView().registerOAuthHandler()))
    }
}

#endif
#if canImport(AppKit)
@available(macOS 11.0, *)
extension NSViewController {
    public func registerOAuthHandler() {
        self.addChild(NSHostingController(rootView: EmptyView().registerOAuthHandler()))
    }
}
#endif
