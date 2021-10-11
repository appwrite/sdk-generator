//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

class iOSDeviceInfo : DeviceInfo {
    
    var string: String
    
    let name: String
    let systemName: String
    let systemVersion: String
    let model: String
    let localizedModel: String
    let identifierForVendor: String
    let utsname: utsname
    
    internal init(
        name: String,
        systemName: String,
        systemVersion: String,
        model: String,
        localizedModel: String,
        identifierForVendor: String,
        utsname: utsname
    ) {
        self.name = name
        self.systemName = systemName
        self.systemVersion = systemVersion
        self.model = model
        self.localizedModel = localizedModel
        self.identifierForVendor = identifierForVendor
        self.utsname = utsname
    }
    
    public static func get() -> AppleDeviceInfo {
        
        #if os(iOS) || os(watchOS) || os(tvOS)
        let device = UIKit.UIDevice.current
        #else
        let device = AppKit.UIDevice.current
        #endif
        
        return AppleDeviceInfo(
            name: device.name,
            systemName: device.systemName,
            systemVersion: device.systemVersion,
            model: device.model,
            localizedModel: device.localizedModel,
            identifierForVendor: device.identifierForVendor.uuidString,
            utsname: utsname
        )
    }
}
