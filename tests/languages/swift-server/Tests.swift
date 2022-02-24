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
            .setEndpointRealtime("wss://demo.appwrite.io/v1")
            .setProject("console")
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        let foo = Foo(client)
        let bar = Bar(client)
        let general = General(client)

        var mock: Mock

        // Foo Tests
        mock = try await foo.get(x: "string", y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.post(x: "string", y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.put(x: "string", y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.patch(x: "string", y: 123, z: ["string in array"])
        print(mock.result)

        mock = try await foo.delete(x: "string", y: 123, z: ["string in array"])
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

        let url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
        let buffer = ByteBuffer(data: try! Data(contentsOf: url))
        let file = File(name: "file.png", buffer: buffer)
        mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file)
        print(mock.result)

        do {
            try await general.error400()
        } catch let error as AppwriteError {
            print(error.message)
        }

        do {
            try await general.error500()
        } catch let error as AppwriteError {
            print(error.message)
        }

        do {
            try await general.error502()
        } catch let error as AppwriteError {
            print(error.message)
        }
    }
}
