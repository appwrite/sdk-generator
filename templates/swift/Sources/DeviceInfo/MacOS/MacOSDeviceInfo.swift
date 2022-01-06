#if os(macOS)
import Foundation

class MacOSDeviceInfo : DeviceInfo {
    let computerName = Sysctl.hostName
    let hostName = Sysctl.osType
    let arch = Sysctl.machine
    let model = Sysctl.model
    let kernelVersion = Sysctl.version
    let osRelease = Sysctl.osRelease
    let activeCPUs = Sysctl.activeCPUs
    
    public static func get() -> MacOSDeviceInfo {
        return MacOSDeviceInfo()
    }
}
#endif
