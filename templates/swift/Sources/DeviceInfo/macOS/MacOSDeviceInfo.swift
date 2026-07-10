#if os(macOS)
import Foundation

class MacOSDeviceInfo {
    let computerName: String
    let hostName: String
    let arch: String
    let model: String
    let kernelVersion: String
    let osRelease: String
    let activeCPUs: Int

    public init() {
        computerName = Sysctl.hostName
        hostName = Sysctl.osType
        arch = Sysctl.machine
        model = Sysctl.model
        kernelVersion = Sysctl.version
        osRelease = Sysctl.osRelease
        activeCPUs = Int(Sysctl.activeCPUs)
    }
}
#endif
