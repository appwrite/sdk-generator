#if os(Linux)
import Foundation

class LinuxDeviceInfo {

    let name: String
    let version: String
    let id: String
    let idLike: [String]
    let versionCodename: String
    let versionId: String
    let prettyName: String
    let buildId: String
    let variant: String
    let variantId: String
    let machineId: String

    public init() {
        let os = getOsRelease()
        let lsb = getLsbRelease()

        name = os["NAME"] ?? "Linux"
        version = os["VERSION"] ?? lsb["LSB_VERSION"] ?? ""
        id = os["ID"] ?? lsb["DISTRIB_ID"] ?? "linux"
        idLike = os["ID_LIKE"]?.split(separator: " ").map { String($0) } ?? []
        versionCodename = os["VERSION_CODENAME"] ?? lsb["DISTRIB_CODENAME"] ?? ""
        versionId = os["VERSION_ID"] ?? lsb["DISTRIB_RELEASE"] ?? ""
        prettyName = os["PRETTY_NAME"] ?? lsb["DISTRIB_DESCRIPTION"] ?? "Linux"
        buildId = os["BUILD_ID"] ?? ""
        variant = os["VARIANT"] ?? ""
        variantId = os["VARIANT_ID"] ?? ""
        machineId = getMachineId()
    }

    private func getOsRelease() -> [String: String] {
        return tryReadKeyValues(path: "/etc/os-release")
    }

    private func getLsbRelease() -> [String: String] {
        return tryReadKeyValues(path: "/etc/lsb-release")
    }

    private func getMachineId() -> String {
        return tryReadValue(path: "/etc/machine-id")!
    }

    private func tryReadValue(path: String) -> String? {
        let url = URL(fileURLWithPath: path)
        return try! String(contentsOf: url, encoding: .utf8)
    }

    private func tryReadKeyValues(path: String) -> [String: String]  {
        let url = URL(fileURLWithPath: path)
        let string = try! String(contentsOf: url, encoding: .utf8)
        let lines = string.components(separatedBy: .newlines)

        var dict = [String: String]()
        for line in lines {
            let splits = line.split(separator: "=")
            if splits.count > 1 {
                let key = String(splits[0])
                let value = String(splits[1])

                dict[key] = value
            }
        }

        return dict
    }
}
#endif
