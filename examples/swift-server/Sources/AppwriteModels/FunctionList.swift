
/// Functions List
public class FunctionList {

    /// Total number of items available on the server.
    public let sum: Int

    /// List of functions.
    public let functions: [Function]

    init(
        sum: Int,
        functions: [Function]
    ) {
        self.sum = sum
        self.functions = functions
    }

    public static func from(map: [String: Any]) -> FunctionList {
        return FunctionList(
            sum: map["sum"] as! Int,
            functions: (map["functions"] as! [[String: Any]]).map { Function.from(map: $0) }
        )
    }

    public func toMap() -> [String: Any] {
        return [
            "sum": sum as Any,
            "functions": functions.map { $0.toMap() } as Any
        ]
    }
                                                                                                                                                                    
}