import Foundation

open class AppwriteError : Swift.Error, Decodable {

    public let message: String
    public let code: Int?

    init(message: String, code: Int? = nil) {
        self.message = message
        self.code = code
    }
}
