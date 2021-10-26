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
