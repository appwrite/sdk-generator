//
//  File.swift
//  
//
//  Created by Jake Barnby on 7/10/21.
//
import Foundation

protocol PackageInfo

class OSPackageInfo {

    let appleInfo: ApplePackageInfo
    let macOSInfo: MacOSPackageInfo
    let linuxInfo: LinuxPackageInfo
    let windowsInfo: WindowsPackageInfo

    init() {
        #if os(iOS) || os(watchOS) || os(tvOS)
        self.agent = ApplePackageInfo.get()
        #elseif os(macOS)
        self.agent = MacOSPackageInfo.get()
        #elseif os(Linux)
        self.agent = LinuxDeviceInfo.get()
        #elseif os(Windows)
        self.agent = LinuxDeviceInfo.get()
        #endif
    }
}

