import XCTest
import FoundationNetworking
import Appwrite
import AsyncHTTPClient
import NIO


struct TestPayload {
    let response: String

    init(_ response: String) {
        self.response = response
    }
}

class Tests: XCTestCase {

    override func setUp() {
        super.setUp()
        writeToFile(string: "Test Started")
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

        let foo = Foo(client: client)
        let bar = Bar(client: client)
        let general = General(client: client)
        let realtime = Realtime(client: client)
        var realtimeResponse = "Realtime failed!"

        realtime.subscribe(channels: ["tests"], payloadType: TestPayload.self) { message in
            realtimeResponse = message.payload.response
        }

        // Foo Tests
        group.enter()
        foo.get("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        foo.post("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        foo.put("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        foo.patch("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        foo.delete("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()

        // Bar Tests
        group.enter()
        bar.get("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        bar.post("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        bar.put("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        bar.patch("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        bar.delete("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()

        // General Tests
        group.enter()
        general.redirect() { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()

        let url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
        let buffer = ByteBuffer(data: try! Data(contentsOf: url))
        let file = File(name: "file.png", buffer: buffer)
        general.upload("string", 123, ["string in array"], file) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()

        group.enter()
        general.error400() { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        general.error500() { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
        group.enter()
        general.error502() { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()

        writeToFile(string: realtimeResponse)
    }

    private func printResult(_ result: Result<HTTPClient.Response, AppwriteError>) {
        var output: String
        switch result {
        case .failure(let error): output = error.localizedDescription
        case .success(var response):
            let json = response.body!.readString(length: response.body!.readableBytes) ?? ""
            do {
                let responseObj: Response = try JSONDecoder().decode(Response.self, from: json.data(using: .utf8)!)
                output = responseObj.result ?? responseObj.message ?? ""
            } catch let error {
                output = json
            }

        }
        writeToFile(string: output)
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
