import Appwrite
import UserNotifications

class NotificationService: AppwriteNotificationService {
    override func didReceive(_ request: UNNotificationRequest, withContentHandler contentHandler: @escaping (UNNotificationContent) -> Void) {
        NSLog("push-e2e: extension received: %@", request.content.title)
        super.didReceive(request) { content in
            NSLog("push-e2e: extension shows: %@", content.title)
            contentHandler(content)
        }
    }
}
