//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

class AppleDeviceInfo : DeviceInfo {
    
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
        let device = UIKit.UIDevice.current
        
        return AppleDeviceInfo(
            name: String,
            systemName: <#T##String#>,
            systemVersion: <#T##String#>,
            model: <#T##String#>,
            localizedModel: <#T##String#>,
            identifierForVendor: <#T##String#>,
            utsname: utsname
        )
    }
}
