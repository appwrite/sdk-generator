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
        print( "Test Started")
    }

    override func tearDown() {
        super.tearDown()
    }

    func test() throws {
        let group = DispatchGroup()
        let client = Client()
            .setEndpointRealtime("ws://demo.appwrite.io/v1")
            .setProject("console")
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        let foo = Foo(client)
        let bar = Bar(client)
        let general = General(client)
        let realtime = Realtime(client)
        var realtimeResponse = "Realtime failed!"

        let expectation = XCTestExpectation(description: "realtime server")    
        _ = realtime.subscribe(channels: ["tests"]) { message in
            realtimeResponse = message.payload!["response"] as! String
            expectation.fulfill()
        }
        
        // Foo Tests
        group.enter()
        foo.get(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.post(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.put(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.patch(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.delete(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()

        // Bar Tests
        group.enter()
        bar.get(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.post(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.put(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.patch(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.delete(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()

        // General Tests
        group.enter()
        general.redirect() { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print((mock as! [String: Any])["result"] as! String)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
        var buffer = ByteBuffer(data: try! Data(contentsOf: url))
        var file = File(name: "file.png", buffer: buffer)
        general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/large_file.mp4")
        buffer = ByteBuffer(data: try! Data(contentsOf: url))
        file = File(name: "large_file.mp4", buffer: buffer)
        general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil) { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let mock): print(mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        general.error400() { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let error): print(error.message)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        general.error500() { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let error): print(error.message)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        general.error502() { result in
            switch result {
            case .failure(let error): print(error.message)
            case .success(let error): print((error as! Error).message)
            }
            group.leave()
        }
        group.wait()

        wait(for: [expectation], timeout: 10.0)
        print( realtimeResponse)

        group.enter()
        general.setCookie() { result in
            switch result {
            case .failure(let error): print( error.message)
            case .success(let mock): print( mock.result)
            }
            group.leave()
        }
        group.wait()

        group.enter()
        general.getCookie() { result in
            switch result {
            case .failure(let error): print( error.message)
            case .success(let mock): print( mock.result)
            }
            group.leave()
        }
        group.wait()
    }
}
