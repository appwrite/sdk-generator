#if os(Windows)
import Foundation

class WindowsDeviceInfo {

    let numberOfCores: String
    let computerName: String
    let systemMemoryInMegabytes: UInt64

    public init() {
        numberOfCores = ProcessInfo.processInfo.processorCount.description
        computerName = Host.current().localizedName ?? ""
        systemMemoryInMegabytes = ProcessInfo.processInfo.physicalMemory / 1000 / 1000 // Bytes to MB
    }
}
#endif