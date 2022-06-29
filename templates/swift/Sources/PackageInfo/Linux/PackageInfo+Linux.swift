import Foundation

extension PackageInfo {

    public static func getLinuxPackage() -> PackageInfo {
        let version = getVersionJson()

        return PackageInfo(
            appName: version?["appName"] as? String ?? "",
            version: version?["version"] as? String ?? "",
            buildNumber: version?["buildNumber"] as? String ?? "",
            packageName: version?["packageName"] as? String ?? "",
            buildSignature: version?["buildSignature"] as? String ?? ""
        )
    }

    private static func getVersionJson() -> [String: Any]? {
        let exePath = URL(fileURLWithPath: "/proc/self/exe").resolvingSymlinksInPath()
        let appPath = exePath.deletingLastPathComponent()
        let jsonPath = appPath.appendingPathComponent("version.json")
        return try? JSONSerialization.jsonObject(with: Data(contentsOf: jsonPath)) as? [String: Any]
    }
}
