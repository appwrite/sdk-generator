#if os(watchOS)
import Foundation
import WatchKit

public extension WKInterfaceDevice {

    static let modelName: String = {
        var systemInfo = utsname()
        uname(&systemInfo)
        let machineMirror = Mirror(reflecting: systemInfo.machine)
        let identifier = machineMirror.children.reduce("") { identifier, element in
            guard let value = element.value as? Int8, value != 0 else { return identifier }
            return identifier + String(UnicodeScalar(UInt8(value)))
        }

        func mapToDevice(identifier: String) -> String {
            switch identifier {
            case "Watch1,1": return "Apple Watch Series 0 38mm"
            case "Watch1,2": return"Apple Watch Series 0 42mm"
            case "Watch2,3": return "Apple Watch Series 2 38mm"
            case "Watch2,4": return "Apple Watch Series 2 42mmm"
            case "Watch2,6": return "Apple Watch Series 1 38mm"
            case "Watch2,7": return "Apple Watch Series 1 42mm"
            case "Watch3,1": return "Apple Watch Series 3 38mm Cellular"
            case "Watch3,2": return "Apple Watch Series 3 42mm Cellular"
            case "Watch3,3": return "Apple Watch Series 3 38mm"
            case "Watch3,4": return "Apple Watch Series 3 42mm"
            case "Watch4,1": return "Apple Watch Series 4 40mm"
            case "Watch4,2": return "Apple Watch Series 4 44mm"
            case "Watch4,3": return "Apple Watch Series 4 40mm Cellular"
            case "Watch4,4": return "Apple Watch Series 4 44mm Cellular"
            case "Watch5,1": return "Apple Watch Series 5 40mm"
            case "Watch5,2": return "Apple Watch Series 5 44mm"
            case "Watch5,3": return "Apple Watch Series 5 40mm Cellular"
            case "Watch5,4": return "Apple Watch Series 5 44mm Cellular"
            case "Watch5,9": return "Apple Watch SE 40mm"
            case "Watch5,10": return "Apple Watch SE 44mm"
            case "Watch5,11": return "Apple Watch SE 40mm Cellular"
            case "Watch5,12": return "Apple Watch SE 44mm Cellular"
            case "Watch6,1": return "Apple Watch Series 6 40mm"
            case "Watch6,2": return "Apple Watch Series 6 44mm"
            case "Watch6.3": return "Apple Watch Series 6 40mm Cellular"
            case "Watch6,4": return "Apple Watch Series 6 44mm Cellular"
            case "Watch6,6": return "Apple Watch Series 7 41mm"
            case "Watch6,7": return "Apple Watch Series 7 45mm"
            case "Watch6,8": return "Apple Watch Series 7 41mm Cellular"
            case "Watch6,9": return "Apple Watch Series 7 45mm Cellular"
            case "Watch6,10": return "Apple Watch SE (2nd generation) 41mm"
            case "Watch6,11": return "Apple Watch SE (2nd generation) 45mm"
            case "Watch6,12": return "Apple Watch SE (2nd generation) 41mm Cellular"
            case "Watch6,13": return "Apple Watch SE (2nd generation) 45mm Cellular"
            case "Watch6,14": return "Apple Watch Series 8 41mm"
            case "Watch6,15": return "Apple Watch Series 8 45mm"
            case "Watch6,16": return "Apple Watch Series 8 41mm Cellular"
            case "Watch6,17": return "Apple Watch Series 8 45mm Cellular"
            case "Watch6,18": return "Apple Watch Ultra"
            default: return "unknown"
            }
        }

        return mapToDevice(identifier: identifier)
    }()

}
#endif
