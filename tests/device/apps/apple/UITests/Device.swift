import XCTest

/// Steps of tests/device/run-apple.php that need a tap. Each runs on its own, with
/// TEST_RUNNER_<name> environment variables for its arguments.
final class Device: XCTestCase {
    private let springboard = XCUIApplication(bundleIdentifier: "com.apple.springboard")
    private let app = XCUIApplication(bundleIdentifier: "io.appwrite.pushdevice.apple")

    /// Allow notifications in the permission alert the SDK raised.
    func testAllow() {
        let allow = springboard.alerts.buttons["Allow"]
        guard allow.waitForExistence(timeout: 30) else {
            XCTFail("no permission alert:\n\(springboard.debugDescription)")
            return
        }
        allow.tap()
    }

    /// Open Notification Center and tap the notification whose text holds TITLE. A tap on a
    /// stack only expands it, so tap again until the app is in front.
    func testOpen() {
        let title = ProcessInfo.processInfo.environment["TITLE"] ?? ""
        let top = springboard.coordinate(withNormalizedOffset: CGVector(dx: 0.2, dy: 0.005))
        top.press(forDuration: 0.1, thenDragTo: springboard.coordinate(withNormalizedOffset: CGVector(dx: 0.2, dy: 0.7)))
        let matches = springboard.descendants(matching: .any).matching(NSPredicate(format: "label CONTAINS %@", title))
        guard matches.firstMatch.waitForExistence(timeout: 15) else {
            XCTFail("no notification \(title):\n\(springboard.debugDescription)")
            return
        }
        // A notification under others in a stack is in the tree but cannot be tapped: tap the one on top.
        for _ in 0..<3 {
            guard let notification = matches.allElementsBoundByIndex.first(where: { $0.isHittable }) else {
                break
            }
            notification.coordinate(withNormalizedOffset: CGVector(dx: 0.5, dy: 0.5)).tap()
            if app.wait(for: .runningForeground, timeout: 5) {
                return
            }
        }
        XCTFail("the app did not open:\n\(springboard.debugDescription)")
    }
}
