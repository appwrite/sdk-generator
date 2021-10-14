import XCTest
#if canImport(FoundationNetworking)
import FoundationNetworking
#endif
import Appwrite
import AsyncHTTPClient
import NIO

class Tests: XCTestCase {

    override func setUp() {
        super.setUp()
        self.writeToFile(string: "Test Started")
    }

    override func tearDown() {
        super.tearDown()
    }

    func test() throws {
        let group = DispatchGroup()

        let client = Client()
            .setEndpointRealtime("wss://demo.appwrite.io/v1")
            .setProject("console")
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        let foo = Foo(client)
        let bar = Bar(client)
        let general = General(client)
        let realtime = Realtime(client)
        var realtimeResponse = "Realtime failed!"

        realtime.subscribe(channels: ["tests"]) { message in
            realtimeResponse = message.payload!["response"] as! String
        }

        // Foo Tests
        group.enter()
        foo.get(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.post(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.put(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.patch(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        foo.delete(x: "string", y: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()

        // Bar Tests
        group.enter()
        bar.get(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.post(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.put(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.patch(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        bar.delete(xrequired: "string", xdefault: 123, z: ["string in array"]) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()

        // General Tests
        group.enter()
        general.redirect() { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: (mock as! [String: Any])["result"] as! String)
            }
            group.leave()
        }
        group.wait()
        group.enter()

        let url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
        let buffer = ByteBuffer(data: try! Data(contentsOf: url))
        let file = File(name: "file.png", buffer: buffer)
        general.upload(x: "string", y: 123, z: ["string in array"], file: file) { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let mock): self.writeToFile(string: mock.result)
            }
            group.leave()
        }
        group.wait()

        group.enter()
        general.error400() { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let error): self.writeToFile(string: error.message)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        general.error500() { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let error): self.writeToFile(string: error.message)
            }
            group.leave()
        }
        group.wait()
        group.enter()
        general.error502() { result in
            switch result {
            case .failure(let error): self.writeToFile(string: error.message)
            case .success(let error): self.writeToFile(string: (error as! Error).message)
            }
            group.leave()
        }
        group.wait()

        self.writeToFile(string: realtimeResponse)
    }

    private func writeToFile(string: String) {
        let url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../../result.txt")
        try! string.appendLine(to: url)
    }
}

struct Response: Decodable {
    let result: String?
    let message: String?
}

extension String {
    func appendLine(to url: URL) throws {
        try self.appending("\n").append(to: url)
    }

    func append(to url: URL) throws {
        let data = self.data(using: .utf8)
        try data?.append(to: url)
    }
}

extension Data {
    func append(to url: URL) throws {
        if let fileHandle = try? FileHandle(forWritingTo: url) {
            defer {
                fileHandle.closeFile()
            }
            fileHandle.seekToEndOfFile()
            fileHandle.write(self)
        } else {
            try write(to: url)
        }
    }
}