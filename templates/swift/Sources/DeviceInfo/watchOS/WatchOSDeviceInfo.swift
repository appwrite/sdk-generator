#if os(watchOS)
import Foundation
import WatchKit

class WatchOSDeviceInfo {

    let name: String
    let systemName: String
    let systemVersion: String
    let model: String
    let localizedModel: String
    let identifierForVendor: String
    let modelIdentifier: String

    public init() {
        let device = WKInterfaceDevice.current()

        name = device.name
        systemName = device.systemName
        systemVersion = device.systemVersion
        model = device.model
        localizedModel = device.localizedModel
        identifierForVendor = device.identifierForVendor?.uuidString ?? ""
        modelIdentifier = WKInterfaceDevice.modelName
    }
}
#endif
