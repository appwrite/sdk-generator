import JSONCodable
import FirebaseCore
import FirebaseMessaging
import AsyncHTTPClient

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

public enum Provider {
    case fcm, apns
}

open class NotificationHandler {
    public static let shared = NotificationHandler()

    internal var provider: Provider = .apns
    internal var client: Client? = nil
    internal var account: Account? = nil
    internal var providerId: String? = nil

    public func setup(
        _ application: Application,
        delegate: AppwriteDelegate,
        provider: Provider
    ) {
        self.provider = provider

        FirebaseApp.configure()

        FirebaseMessaging.Messaging.messaging().delegate = delegate

        UNUserNotificationCenter.current().delegate = delegate

        NotificationCenter.default.addObserver(
            self,
            selector: #selector(tokenRefreshNotification),
            name: Notification.Name.MessagingRegistrationTokenRefreshed,
            object: nil
        )

        Client.cookieListener = { (existing, new) in
            let group = DispatchGroup()

            group.enter()

            Task {
                guard let token = try? await FirebaseMessaging.Messaging.messaging().token() else {
                     return
                }

                await self.updateTarget(
                    existingCookies: existing,
                    newCookies: new,
                    token: token
                )

                group.leave()
            }

            group.wait()
        }

        let options: UNAuthorizationOptions = [.alert, .badge, .sound]

        UNUserNotificationCenter.current().requestAuthorization(
            options: options,
            completionHandler: { granted, error in
                if (granted) {
                    DispatchQueue.main.async {
                        application.registerForRemoteNotifications()
                    }
                }
            }
        )
    }

    @objc func tokenRefreshNotification(_ notification: Notification) {
        guard let token = notification.object as? String else {
            return
        }

        handleFCMToken(token)
    }

    public func handleAPNSToken(_ token: Data) {
        switch (provider) {
        case .fcm:
            FirebaseMessaging.Messaging.messaging().apnsToken = token
        case .apns:
            Task {
                await self.updateTarget(
                    existingCookies: [],
                    newCookies: [],
                    token: token.map { String(format: "%.2hhx", $0) }.joined()

                )
            }
        }
    }

    public func handleFCMToken(_ token: String) {
        Task {
            await self.updateTarget(
                existingCookies: [],
                newCookies: [],
                token: token
            )
        }
    }

    public func updateTarget(
        existingCookies: [String],
        newCookies: [String],
        token: String
    ) async {
        if (client == nil) {
            return
        }

        if (account == nil) {
            account = Account(client!)
        }

        let currentToken = UserDefaults.standard.string(forKey: "pushToken")
        var currentTargetId = UserDefaults.standard.string(forKey: "targetId") ?? ""

        var existing = [String]()
        if (existingCookies.isEmpty) {
            let domain = URL(string: client!.endPoint)!.host!
            let cookies = UserDefaults.standard.stringArray(forKey: domain) ?? []

            cookies.forEach {
                existing.append($0)
            }
        }

        var existingUser: [String: Any]? = nil
        if (existing.isEmpty && !newCookies.isEmpty) {
            existingUser = try? await request(
                method: "GET",
                path: "/account",
                headers: ["cookie": newCookies.joined(separator: "; ")]
            )
        } else if (!existing.isEmpty) {
            existingUser = try? await request(
                method: "GET",
                path: "/account",
                headers: ["cookie": existingCookies.joined(separator: "; ")]
            )
        }

        if (existingUser == nil) {
            return
        }

        var newUser: [String: Any]? = nil
        if (!newCookies.isEmpty) {
            newUser = try? await request(
                method: "GET",
                path: "/account",
                headers: ["cookie": newCookies.joined(separator: "; ")]
            )
        }

        let existingUserId = existingUser?["$id"] as? String
        let newUserId = newUser?["$id"] as? String

        if (
            token == currentToken
            && (!existingCookies.isEmpty && existingUserId == newUserId)
        ) {
            return
        }

        UserDefaults.standard.set(token, forKey: "pushToken")

        if (!existing.isEmpty && existingUserId != newUserId) {
            if (!currentTargetId.isEmpty) {
                 let result = try? await request(
                    method: "DELETE",
                    path: "/account/targets/$currentTargetId/push",
                    headers: ["cookie": existing.joined(separator: "; ")]
                )

                if (result == nil) {
                    return
                }

                UserDefaults.standard.removeObject(forKey: "targetId")
                currentTargetId = ""
            }
        }

        let target: [String: Any]?

        var params = [
            "targetId": ID.unique(),
            "identifier": token
        ]

        if (providerId != nil) {
            params["providerId"] = providerId!
        }

        if ((currentTargetId.isEmpty && existing.isEmpty) || existingUserId != newUserId) {
            target = try? await request(
                method: "POST",
                path: "/account/targets/push",
                headers: ["cookie": newCookies.joined(separator: "; ")],
                body: params
            )
        } else {
            target = try? await request(
                method: "POST",
                path: "/account/targets/push",
                headers: ["cookie": existing.joined(separator: "; ")],
                body: params
            )
        }

        if (target == nil) {
            return
        }

        UserDefaults.standard.set(target?["$id"] ?? "", forKey: "targetId")
    }

    private func request(
        method: String,
        path: String,
        headers: [String: String],
        body: [String: Any]? = nil
    ) async throws -> [String: Any]? {
        var request = HTTPClientRequest(url: client!.endPoint + path)

        request.method = .RAW(value: method)

        for (key, value) in client!.headers.merging(headers, uniquingKeysWith: { $1 }) {
            request.headers.add(name: key, value: value)
        }

        if (body != nil) {
            guard let json = try? JSONSerialization.data(withJSONObject: body!, options: []) else {
                return nil
            }

            request.body = .bytes(json)
        }

        guard let response = try? await client!.http.execute(
            request,
            timeout: .seconds(30)
        ) else {
            return nil
        }

        guard let data = try? await response.body.collect(upTo: Int.max) else {
            return nil
        }

        guard let dict = try? JSONSerialization.jsonObject(with: data) as? [String: Any] else {
            return nil
        }

        return dict
    }
}
