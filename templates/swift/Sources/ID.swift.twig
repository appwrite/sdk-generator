import Foundation

public class ID {
    // Generate an hex ID based on timestamp
    // Recreated from https://www.php.net/manual/en/function.uniqid.php
    private static func hexTimestamp() -> String {
        let now = Date()
        let secs = Int(now.timeIntervalSince1970)
        let usec = Int((now.timeIntervalSince1970 - Double(secs)) * 1_000_000)
        let hexTimestamp = String(format: "%08x%05x", secs, usec)
        return hexTimestamp
    }

    public static func custom(_ id: String) -> String {
        return id
    }

    // Generate a unique ID with padding to have a longer ID
    public static func unique(padding: Int = 7) -> String {
        let baseId = Self.hexTimestamp()
        let randomPadding = (1...padding).map {
            _ in String(format: "%x", Int.random(in: 0..<16))
        }.joined()
        return baseId + randomPadding
    }
}
