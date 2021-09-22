import XCTest
import FoundationNetworking
import Appwrite
import AsyncHTTPClient
import NIO

class Tests: XCTestCase {

    override func setUp() {
        super.setUp()
        // Put setup code here. This method is called before the invocation of each test method in the class.
    }

    override func tearDown() {
        // Put teardown code here. This method is called after the invocation of each test method in the class.
        super.tearDown()
    }

    func test() throws {
        let group = DispatchGroup()

        let client = Client()
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        let foo = Foo(client: client)
        let bar = Bar(client: client)
        let general = General(client: client)

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
        let buffer = ByteBuffer(data: url.dataRepresentation)
        let file = File(name: "file.png", buffer: buffer)
        general.upload("string", 123, ["string in array"], file) { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
    }

    private func printResult(_ result: Result<HTTPClient.Response, AppwriteError>) {
        var output: String
        switch result {
        case .failure(let error): output = error.localizedDescription
        case .success(var response):
            let json = response.body!.readString(length: response.body!.readableBytes) ?? ""
            let responseObj: Response = try! JSONDecoder().decode(Response.self, from: json.data(using: .utf8)!)
            output = responseObj.result ?? responseObj.message ?? ""
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
