import SwiftUI
import Appwrite
import NIO
import Firebase
import FirebaseMessaging

let host = "https://cloud.appwrite.io/v1"
let projectId = "[YOUR_PROJECT_ID]"

let client = Client()
    .setEndpoint(host)
    .setProject(projectId)

let account = Account(client)
let storage = Storage(client)
let realtime = Realtime(client)

class AppDelegate: NSObject, UIApplicationDelegate, UNUserNotificationCenterDelegate, MessagingDelegate {

    func application(
        _ application: UIApplication,
        didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey : Any]? = nil
    ) -> Bool {
        FirebaseApp.configure()

        Messaging.messaging().delegate = self

        UNUserNotificationCenter.current().delegate = self

        let authOptions: UNAuthorizationOptions = [.alert, .badge, .sound]
        UNUserNotificationCenter.current().requestAuthorization(
          options: authOptions,
          completionHandler: { granted, error in
              DispatchQueue.main.async {
                  if granted {
                      application.registerForRemoteNotifications()
                  }
              }
          }
        )

        return true
    }

    func messaging(
        _ messaging: FirebaseMessaging.Messaging,
        didReceiveRegistrationToken fcmToken: String?
    ) {
        guard let fcmToken = fcmToken else {
            return
        }

        UserDefaults.standard.set(fcmToken , forKey: "fcmToken")

        let targetId = UserDefaults.standard.string(forKey: "targetId")

        Task {
            do {
                _ = try await account.get()
            } catch {
                return
            }

            if targetId == nil {
                let target = try? await account.createPushTarget(
                    targetId: ID.unique(),
                    identifier: fcmToken
                )

                UserDefaults.standard.set(target?.id , forKey: "targetId")
            } else {
                _ = try? await account.updatePushTarget(
                    targetId: targetId!,
                    identifier: fcmToken
                )
            }
        }
    }

    func application(
        _ application: UIApplication,
        didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data
    ) {
        Messaging.messaging().apnsToken = deviceToken
    }
}

@main
struct ExampleApp: App {
    @UIApplicationDelegateAdaptor(AppDelegate.self) var delegate

    var body: some Scene {
        WindowGroup {
            ExampleView(viewModel: ExampleView.ViewModel())
        }
    }
}
