import Foundation

extension PackageInfo {
    
    public static func getApplePackage() -> PackageInfo {
        let bundle = Bundle.main
        
        let appName = bundle.object(forInfoDictionaryKey: "CFBundleDisplayName") as? String
            ?? bundle.object(forInfoDictionaryKey: "CFBundleName") as? String
            ?? ""
        
        let packageName = bundle.bundleIdentifier!
        let version = bundle.object(forInfoDictionaryKey: "CFBundleShortVersionString") as? String ?? ""
        let build = bundle.object(forInfoDictionaryKey: "CFBundleVersion") as? String ?? ""
        
        return PackageInfo(
            appName: appName,
            version: version,
            buildNumber: build,
            packageName: packageName
        )
    }
}
