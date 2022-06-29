import XCTest
import Foundation
#if canImport(FoundationNetworking)
import FoundationNetworking
#endif
import Appwrite
import AsyncHTTPClient
import NIO

class Tests: XCTestCase {

    override func setUp() {
        super.setUp()
        print("Test Started")
    }

    override func tearDown() {
        super.tearDown()
    }

    func test() async throws {
        let client = Client()
            .setProject("console")
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        let foo = Foo(client, "string")
        let bar = Bar(client)
        let general = General(client)

        var mock: Mock

        // Foo Tests
        mock = try await foo.get(y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.post(y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.put(y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.patch(y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.delete(y: 123, z: ["string in array"])
        print(mock.result)


        // Bar Tests
        mock = try await bar.get(xrequired: "string", xdefault: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.post(xrequired: "string", xdefault: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.put(xrequired: "string", xdefault: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.patch(xrequired: "string", xdefault: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.delete(xrequired: "string", xdefault: 123, z: ["string in array"])
        print(mock.result)

        // General Tests
        let result = try await general.redirect()
        print((result as! [String: Any])["result"] as! String)

        do {
            var file = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch let error as AppwriteError {
            print(error.message)
        }

        do {
            var file = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../resources/large_file.mp4")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch let error as AppwriteError {
            print(error.message)
        }

        do {
            var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
            var buffer = ByteBuffer(data: try! Data(contentsOf: url))
            var file = InputFile.fromBuffer(buffer, filename: "file.png", mimeType: "image/png")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch let error as AppwriteError {
            print(error.message)
        }

        do {
            var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/large_file.mp4")
            var buffer = ByteBuffer(data: try! Data(contentsOf: url))
            var file = InputFile.fromBuffer(buffer, filename: "large_file.mp4", mimeType: "video/mp4")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch let error as AppwriteError {
            print(error.message)
        }

        var res = try await general.download()
        print(res.readString(length: res.readableBytes)!)

        // Exception Tests
        do {
            try await general.error400()
        } catch let error as AppwriteError {
            print(error.message)
        }

        do {
            try await general.error500()
        } catch {
            print(error.localizedDescription)
        }

        do {
            try await general.error502()
        } catch {
            print(String(describing: error))
        }

        try! await general.empty()
    }
}
