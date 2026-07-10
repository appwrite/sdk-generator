import Foundation

class PackageInfo {
        
    let appName: String
    let version: String
    let buildNumber: String
    let packageName: String
    let buildSignature: String?
    
    internal init(
        appName: String,
        version: String,
        buildNumber: String,
        packageName: String,
        buildSignature: String? = nil
    ) {
        self.appName = appName
        self.version = version
        self.buildNumber = buildNumber
        self.packageName = packageName
        self.buildSignature = buildSignature
    }
}
