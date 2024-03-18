#if os(iOS) || os(tvOS) || os(visionOS)
import Foundation
import UIKit

class IOSDeviceInfo {

    let name: String
    let systemName: String
    let systemVersion: String
    let model: String
    let localizedModel: String
    let identifierForVendor: String
    let modelIdentifier: String

    public init() {
        let device = UIDevice.current

        name =  device.name
        systemName = device.systemName
        systemVersion = device.systemVersion
        model = device.model
        localizedModel = device.localizedModel
        identifierForVendor = device.identifierForVendor?.uuidString ?? ""
        modelIdentifier = UIDevice.modelName
    }
}
#endif
