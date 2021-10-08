//
//  File.swift
//  
//
//  Created by Jake Barnby on 8/10/21.
//

import Foundation

class LinuxPackageInfo {
    
    let appName: String
    let version: String
    let buildNumber: String
    let packageName: String
    let buildSignature: String
    
    internal init(appName: String, version: String, buildNumber: String, packageName: String, buildSignature: String) {
        self.appName = appName
        self.version = version
        self.buildNumber = buildNumber
        self.packageName = packageName
        self.buildSignature = buildSignature
    }
    
    public static func getLinuxPackage() -> LinuxPackageInfo {
        let versionJson = getVersionJson()
        
        return LinuxPackageInfo(
            appName: <#T##String#>,
            version: <#T##String#>,
            buildNumber: <#T##String#>,
            packageName: <#T##String#>,
            buildSignature: String
        )
    }
    
    private static func getVersionJson() -> String {
        let url = URL(fileURLWithPath: "/proc/self/exe").resolvingSymlinksInPath()
        
//        final appPath = path.dirname(exePath);
//        final assetPath = path.join(appPath, 'data', 'flutter_assets');
//        final versionPath = path.join(assetPath, 'version.json');
//        return jsonDecode(await File(versionPath).readAsString());
    }
}
