//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

extension PackageInfo {

    public static func getLinuxPackage() -> PackageInfo {
        let version = getVersionJson()

        return PackageInfo(
            appName: version["app_name"] as! String,
            version: version["version"] as! String,
            buildNumber: version["build_number"] as! String,
            packageName: "",
            buildSignature: ""
        )
    }

    private static func getVersionJson() -> [String: Any] {
        let exePath = URL(fileURLWithPath: "/proc/self/exe").resolvingSymlinksInPath()
        let appPath = exePath.deletingLastPathComponent()
        let jsonPath = appPath.appendingPathComponent("version.json")
        return try! JSONSerialization
            .jsonObject(with: Data(contentsOf: jsonPath)) as! [String: Any]
    }
}
