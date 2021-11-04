#if os(Windows)
import Foundation

class WindowsDeviceInfo : DeviceInfo {
    
    let numberOfCores: String
    let computerName: String
    let systemMemoryInMegabytes: UInt64
    
    public init(
        numberOfCores: String,
        computerName: String,
        systemMemoryInMegabytes: UInt64
    ) {
        self.numberOfCores = numberOfCores
        self.computerName = computerName
        self.systemMemoryInMegabytes = systemMemoryInMegabytes
    }

    public static func get() -> WindowsDeviceInfo {
        let memory = ProcessInfo.processInfo.physicalMemory / 1000 / 1000 // Bytes to MB
        
        return WindowsDeviceInfo(
            numberOfCores: ProcessInfo.processInfo.processorCount.description,
            computerName: Host.current().localizedName ?? "",
            systemMemoryInMegabytes: memory
        )
    }
}
#endif
 
