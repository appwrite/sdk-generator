//
//  File.swift
//  
//
//  Created by Jake Barnby on 11/10/21.
//
import Foundation

class MacOSDeviceInfo : DeviceInfo {
    let computerName = Sysctl.hostName
    let hostName = Sysctl.osType
    let arch = Sysctl.machine
    let model = Sysctl.model
    let kernelVersion = Sysctl.version
    let osRelease = Sysctl.osRelease
    let activeCPUs = Sysctl.activeCPUs
    let memorySize = Sysctl.memSize
    let cpuFrequency = Sysctl.cpuFreq
    
    public static get() -> MacOSDeviceInfo {
        return MacOSDeviceInfo()
    }
}
