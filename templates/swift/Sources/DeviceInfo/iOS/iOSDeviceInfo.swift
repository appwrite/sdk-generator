//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

#if canImport(UIKit)
import UIKit
#endif

class iOSDeviceInfo : DeviceInfo {
    
    let name: String
    let systemName: String
    let systemVersion: String
    let model: String
    let localizedModel: String
    let identifierForVendor: String
    let modelIdentifier: String
    
    internal init(
        name: String,
        systemName: String,
        systemVersion: String,
        model: String,
        localizedModel: String,
        identifierForVendor: String,
        modelIdentifier: String
    ) {
        self.name = name
        self.systemName = systemName
        self.systemVersion = systemVersion
        self.model = model
        self.localizedModel = localizedModel
        self.identifierForVendor = identifierForVendor
        self.modelIdentifier = modelIdentifier
    }
    
    #if os(iOS) || os(watchOS) || os(tvOS)
    public static func get() -> iOSDeviceInfo {
        let device = UIDevice.current
        
        return iOSDeviceInfo(
            name: device.name,
            systemName: device.systemName,
            systemVersion: device.systemVersion,
            model: device.model,
            localizedModel: device.localizedModel,
            identifierForVendor: device.identifierForVendor?.uuidString ?? "",
            modelIdentifier: UIDevice.modelName
        )
    }
    #endif
}
