import Foundation

class OSDeviceInfo {

    #if os(iOS) || os(tvOS) || os(visionOS)
    var iOSInfo: IOSDeviceInfo?
    #elseif os(watchOS)
    var watchOSInfo: WatchOSDeviceInfo?
    #elseif os(macOS)
    var macOSInfo: MacOSDeviceInfo?
    #elseif os(Linux)
    var linuxInfo: LinuxDeviceInfo?
    #elseif os(Windows)
    var windowsInfo: WindowsDeviceInfo?
    #endif

    init() {
        #if os(iOS) || os(tvOS) || os(visionOS)
        self.iOSInfo = IOSDeviceInfo()
        #elseif os(watchOS)
        self.watchOSInfo = WatchOSDeviceInfo()
        #elseif os(macOS)
        self.macOSInfo = MacOSDeviceInfo()
        #elseif os(Linux)
        self.linuxInfo = LinuxDeviceInfo()
        #elseif os(Windows)
        self.windowsInfo = LinuxDeviceInfo()
        #endif
    }
}

