//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

extension PackageInfo {

    public static func getLinuxPackage() -> PackageInfo {
        let versionJson = getVersionJson()
        
        return PackageInfo(
            appName: "",
            version: "",
            buildNumber: "",
            packageName: "",
            buildSignature: ""
        )
    }
    
    private static func getVersionJson() -> String {
        let exePath = URL(fileURLWithPath: "/proc/self/exe").resolvingSymlinksInPath()
        let appPath = exePath.deletingLastPathComponent()
        let resolved = appPath.appendingPathComponent("Package.resolved")
        let contents = String(contentsOfFile: resolved.absoluteString)
        let regex = 
        // Package.resolved path?
//        final appPath = path.dirname(exePath);
//        final assetPath = path.join(appPath, 'data', 'flutter_assets');
//        final versionPath = path.join(assetPath, 'version.json');
//        return jsonDecode(await File(versionPath).readAsString());
    }
}
