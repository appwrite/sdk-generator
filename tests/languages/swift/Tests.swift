//  Tests_iOS.swift
//  Tests iOS
//  Created by Jake Barnby on 3/08/21.
//

import XCTest
import Appwrite
import AsyncHTTPClient

class Tests_iOS: XCTestCase {

    override func setUpWithError() throws {
        // Put setup code here. This method is called before the invocation of each test method in the class.
        // In UI tests it is usually best to stop immediately when a failure occurs.
        continueAfterFailure = false
        // In UI tests it’s important to set the initial state - such as interface orientation - required for your tests before they run. The setUp method is a good place to do this.
    }

    override func tearDownWithError() throws {
        // Put teardown code here. This method is called after the invocation of each test method in the class.
    }

    func test() throws {
        let client = Client()
        let foo = Foo(client)
        let bar = Bar(client)
        let general = General(client)
        client.addHeader(key: "Origin", value: "http://localhost")
        client.setSelfSigned()

        var response: HTTPClient.Response
        // Foo Tests

        response = foo.get("string", 123, ["string in array"]).await()
        printResponse(response)
        response = foo.post("string", 123, ["string in array"]).await()
        printResponse(response)
        response = foo.put("string", 123, ["string in array"]).await()
        printResponse(response)
        response = foo.patch("string", 123, ["string in array"]).await()
        printResponse(response)
        response = foo.delete("string", 123, ["string in array"]).await()
        printResponse(response)

        // Bar Tests
        response = bar.get("string", 123, ["string in array"]).await()
        printResponse(response)
        response = bar.post("string", 123, ["string in array"]).await()
        printResponse(response)
        response = bar.put("string", 123, ["string in array"]).await()
        printResponse(response)
        response = bar.patch("string", 123, ["string in array"]).await()
        printResponse(response)
        response = bar.delete("string", 123, ["string in array"]).await()
        printResponse(response)

        // General Tests
        response = general.redirect()
        printResponse(response)

        response = general.upload("string", 123, ["string in array"], File("../../resources/file.png")).await()
        printResponse(response)

        do {
            try general.error400()
        } catch let error {
            writeToFile(error.message)
        }

        do {
            try general.error500()
        } catch let error {
            writeToFile(error.message)
        }

        do {
            try general.error502()
        } catch let error {
            writeToFile(error.message)
        }

    }

    private func printResponse(response: HTTPClient.Response) {
        // Store the outputs in a file and
        try! writeToFile(String(response))
    }

    private func writeToFile(string: String?) {
        let file = "result.txt"

        guard let dir = FileManager.default.urls(
            for: .applicationDirectory,
            in: .userDomainMask
        ).first() else {
            return
        }

        let fileURL = dir.appendingPathComponent(file)

        try! text.write(to: fileURL, atomically: false, encoding: .utf8)
    }

}
