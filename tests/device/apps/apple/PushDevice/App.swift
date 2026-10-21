import Appwrite
import SwiftUI

// The device tests' user on the mock broker: base64 of {"id": "e2e-device-user", "secret": "device"}.
private let session = "eyJpZCI6ImUyZS1kZXZpY2UtdXNlciIsInNlY3JldCI6ImRldmljZSJ9"

// One event per line, read by the device test driver from the Simulator's log.
func log(_ line: String) {
    NSLog("push-e2e: %@", line)
}

@main
struct PushDeviceApp: App {
    // Created at launch, so the tap that launched the app reaches it.
    private let push: Push

    init() {
        // A fixed client id: the mock broker keeps the persistent session per client id, and the
        // extension connects with the same one.
        let client = Client()
            .setEndpoint("http://127.0.0.1:9/v1")
            .setProject("console")
            .setPushEndpoint("mqtt://127.0.0.1:1883")
            .setPushClientId("e2e-device-apple")
            .setSession(session)
        let push = Push(client)
            .onOpen { log("connected") }
            .onClose { log("disconnected") }
            .onError { log("error: \($0.localizedDescription)") }
        self.push = push

        Task {
            do {
                _ = try await push.subscribe { message in
                    let json = (try? JSONSerialization.jsonObject(with: Data(message.data.utf8))) as? [String: Any]
                    let notification = json?["notification"] as? [String: Any]
                    log("message: \(notification?["title"] as? String ?? "")")
                }
                log("subscribed")
                if let opened = push.getInitialNotification() {
                    log("launched: \(opened.data["saleId"] as? String ?? "")")
                }
                _ = push.onNotificationOpened { opened in
                    log("opened: \(opened.data["saleId"] as? String ?? "")")
                }
            } catch {
                log("error: \(error.localizedDescription)")
            }
        }
    }

    var body: some Scene {
        WindowGroup {
            Text("Push device")
        }
    }
}
