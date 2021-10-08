//
//  File.swift
//  
//
//  Created by Jake Barnby on 7/10/21.
//
import Foundation

protocol DeviceInfo {
    var string: String {get set}
}

class OSDeviceInfo {

    let appleInfo: AppleDeviceInfo
    let macOSInfo: AppleDeviceInfo
    let linuxInfo: LinuxDeviceInfo
    let windowsInfo: WindowsDeviceInfo

    init() {
        #if os(iOS) || os(watchOS) || os(tvOS)
        self.appleInfo = AppleDeviceInfo.get()
        #elseif os(macOS)
        self.macOSInfo = AppleDeviceInfo.get()
        #elseif os(Linux)
        self.linuxInfo = LinuxDeviceInfo.get()
        #elseif os(Windows)
        self.windowsInfo = LinuxDeviceInfo.get()
        #endif
    }
}

