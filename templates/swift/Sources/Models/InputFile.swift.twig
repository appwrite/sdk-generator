import Foundation
import NIO

open class InputFile {

    public var path: String = ""
    public var filename: String = ""
    public var mimeType: String = ""
    public var sourceType: String = ""
    public var data: Any? = nil

    internal init() {
    }

    public static func fromPath(_ path: String) -> InputFile {
        let instance = InputFile()
        instance.path = path
        instance.filename = URL(fileURLWithPath: path, isDirectory: false).lastPathComponent
        instance.mimeType = path.mimeType()
        instance.sourceType = "path"
        return instance
    }

    public static func fromData(_ data: Data, filename: String, mimeType: String) -> InputFile {
        let instance = InputFile()
        instance.filename = filename
        instance.mimeType = mimeType
        instance.sourceType = "data"
        instance.data = data
        return instance
    }

    public static func fromBuffer(_ buffer: ByteBuffer, filename: String, mimeType: String) -> InputFile {
        let instance = InputFile()
        instance.filename = filename
        instance.mimeType = mimeType
        instance.sourceType = "buffer"
        instance.data = buffer
        return instance
    }
}
