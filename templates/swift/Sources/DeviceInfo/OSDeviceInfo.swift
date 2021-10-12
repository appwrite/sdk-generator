//
//  File.swift
//  
//
//  Created by Jake Barnby on 7/10/21.
//
import Foundation

protocol DeviceInfo {}

class OSDeviceInfo {

    var iOSInfo: iOSDeviceInfo?
    var macOSInfo: MacOSDeviceInfo?
    var linuxInfo: LinuxDeviceInfo?
    var windowsInfo: WindowsDeviceInfo?

    init() {
        #if os(iOS) || os(watchOS) || os(tvOS)
        self.iOSInfo = iOSDeviceInfo.get()
        #elseif os(macOS)
        self.macOSInfo = MacOSDeviceInfo.get()
        #elseif os(Linux)
        self.linuxInfo = LinuxDeviceInfo.get()
        #elseif os(Windows)
        self.windowsInfo = LinuxDeviceInfo.get()
        #endif
    }
}

