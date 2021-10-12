import Foundation

protocol DeviceInfo {}

class OSDeviceInfo {

    #if os(iOS) || os(watchOS) || os(tvOS)
    var iOSInfo: iOSDeviceInfo?
    #elseif os(macOS)
    var macOSInfo: MacOSDeviceInfo?
    #elseif os(Linux)
    var linuxInfo: LinuxDeviceInfo?
    #elseif os(Windows)
    var windowsInfo: WindowsDeviceInfo?
    #endif

    init() {
        #if os(iOS) || os(watchOS) || os(tvOS)
        self.iOSInfo = iOSDeviceInfo.get()
        #elseif os(macOS)
        self.macOSInfo = MacOSDeviceInfo.get()
        #elseif os(Linux)
        self.linuxInfo = LinuxDeviceInfo.get()
        #elseif os(Windows)
        self.windowsInfo = LinuxDeviceInfo.get()
        #endif
    }
}

