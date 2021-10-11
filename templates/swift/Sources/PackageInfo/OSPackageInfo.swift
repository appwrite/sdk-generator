//
//  File.swift
//  
//
//  Created by Jake Barnby on 7/10/21.
//
import Foundation

class OSPackageInfo {

    public static func get() -> PackageInfo {
        #if os(iOS) || os(watchOS) || os(tvOS) || os(macOS)
        return PackageInfo.getApplePackage()
        #elseif os(Linux)
        return PackageInfo.getLinuxPackage()
        #elseif os(Windows)
        return PackageInfo.getWindowsPackage()
        #endif
    }
}
