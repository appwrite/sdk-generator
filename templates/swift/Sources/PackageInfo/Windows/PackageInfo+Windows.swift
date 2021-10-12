//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

class WindowsPackageInfo : PackageInfo {
    
    public static func getWindowsPackage() -> PackageInfo {
        return WindowsPackageInfo(
            appName: "",
            version: "",
            buildNumber: "",
            packageName: ""
        )
    }
}
