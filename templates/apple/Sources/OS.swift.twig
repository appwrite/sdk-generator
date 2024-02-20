#if canImport(Darwin)
import Foundation
import UserNotifications

#if os(iOS) || os(tvOS)
import UIKit
public typealias Application = UIApplication
#elseif os(macOS)
import AppKit
public typealias Application = NSApplication
#elseif os(watchOS)
import WatchKit
public typealias Application = WKApplication
#endif

public enum OSPermission {
    case notifications
}

public class OS {
    public static func requestPermission(
        _ application: Application,
        _ permission: OSPermission,
        onGranted: @escaping () -> Void = {},
        onDenied: @escaping () -> Void = {}
    ) {
        switch(permission) {
        case .notifications:
            requestNotificationPermission(
                application,
                onGranted: onGranted,
                onDenied: onDenied
            )
        }
    }

    private static func requestNotificationPermission(
        _ application: Application,
        onGranted: @escaping () -> Void = {},
        onDenied: @escaping () -> Void = {}
    ) {
        let options: UNAuthorizationOptions = [.alert, .badge, .sound]

        UNUserNotificationCenter.current().requestAuthorization(
            options: options,
            completionHandler: { granted, error in
                DispatchQueue.main.async {
                    if (granted) {
                        onGranted()
                        application.registerForRemoteNotifications()
                    }
                    else {
                        onDenied()
                    }
                }
            }
        )
    }
}
#endif
