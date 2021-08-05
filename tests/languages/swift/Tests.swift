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
        group.enter()
        foo.post("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        foo.put("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        foo.patch("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        foo.delete("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }

        // Bar Tests
        group.enter()
        bar.get("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        bar.post("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        bar.put("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        bar.patch("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        bar.delete("string", 123, ["string in array"]) { result in
            self.printResult(result)
            group.leave()
        }

        // General Tests
        group.enter()
        general.redirect() { result in
            self.printResult(result)
            group.leave()
        }

        group.enter()
        let url = URL(string: "file:///../../resources/file.png")!
        var str: String
        do {
            str = try String(contentsOf: url)
        } catch {
            self.printResult(Result.failure(error))
            throw error
        }
        let buffer = ByteBuffer(string: str)
        general.upload("string", 123, ["string in array"], buffer) { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        general.error400() { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        general.error500() { result in
            self.printResult(result)
            group.leave()
        }
        group.enter()
        general.error502() { result in
            self.printResult(result)
            group.leave()
        }
        group.wait()
    }

    private func printResult(_ result: Result<HTTPClient.Response, Error>) {
        var output: String
        switch result {
        case .failure(let error): output = error.localizedDescription
        case .success(var response): output = response.body!.readString(length: response.body!.readableBytes) ?? ""
        }
        print(output)
    }

}
