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
        do {
        let client = Client()
            .setProject("123456")
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        var mock: Mock

        // Ping pong test
        let ping = try await client.ping()
        mock = Mock.from(json: ping)!
        print(mock.result)

        // reset project
        client.setProject("console")

        let foo = Foo(client)
        let bar = Bar(client)
        let general = General(client)

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
        mock = try await bar.get(required: "string", default: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.post(required: "string", default: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.put(required: "string", default: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.patch(required: "string", default: 123, z: ["string in array"])
        print(mock.result)

        mock = try await bar.delete(required: "string", default: 123, z: ["string in array"])
        print(mock.result)


        // General Tests
        let result = try await general.redirect()
        print((result as! [String: Any])["result"] as! String)

        do {
            var file = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            var file = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../resources/large_file.mp4")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/file.png")
            var buffer = ByteBuffer(data: try! Data(contentsOf: url))
            var file = InputFile.fromBuffer(buffer, filename: "file.png", mimeType: "image/png")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../resources/large_file.mp4")
            var buffer = ByteBuffer(data: try! Data(contentsOf: url))
            var file = InputFile.fromBuffer(buffer, filename: "large_file.mp4", mimeType: "video/mp4")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        mock = try await general.xenum(mockType: .first)
        print(mock.result)

        do {
            try await general.error400()
        } catch {
            print(error.localizedDescription)
        }

        do {
            try await general.error500()
        } catch {
            print(error.localizedDescription)
        }

        do {
            try await general.error502()
        } catch {
            print(error.localizedDescription)
        }

        try! await general.empty()

        let url = try? await general.oauth2(
            clientId: "clientId",
            scopes: ["test"],
            state: "123456",
            success: "https://localhost",
            failure: "https://localhost"
        )
        print(url!)

        // Query helper tests
        print(Query.equal("released", value: [true]))
        print(Query.equal("title", value: ["Spiderman", "Dr. Strange"]))
        print(Query.notEqual("title", value: "Spiderman"))
        print(Query.lessThan("releasedYear", value: 1990))
        print(Query.greaterThan("releasedYear", value: 1990))
        print(Query.search("name", value: "john"))
        print(Query.isNull("name"))
        print(Query.isNotNull("name"))
        print(Query.between("age", start: 50, end: 100))
        print(Query.between("age", start: 50.5, end: 100.5))
        print(Query.between("name", start: "Anna", end: "Brad"))
        print(Query.startsWith("name", value: "Ann"))
        print(Query.endsWith("name", value: "nne"))
        print(Query.select(["name", "age"]))
        print(Query.orderAsc("title"))
        print(Query.orderDesc("title"))
        print(Query.cursorAfter("my_movie_id"))
        print(Query.cursorBefore("my_movie_id"))
        print(Query.limit(50))
        print(Query.offset(20))
        print(Query.contains("title", value: "Spider"))
        print(Query.contains("labels", value: "first"))
        print(Query.or(
            [Query.equal("released", value: true), Query.lessThan("releasedYear", value: 1990)]
        ))
        print(Query.and(
            [Query.equal("released", value: false), Query.greaterThan("releasedYear", value: 2015)]
        ))

        // Permission & Role helper tests
        print(Permission.read(Role.any()))
        print(Permission.write(Role.user(ID.custom("userid"))))
        print(Permission.create(Role.users()))
        print(Permission.update(Role.guests()))
        print(Permission.delete(Role.team("teamId", "owner")))
        print(Permission.delete(Role.team("teamId")))
        print(Permission.create(Role.member("memberId")))
        print(Permission.update(Role.users("verified")))
        print(Permission.update(Role.user(ID.custom("userid"), "unverified")))
        print(Permission.create(Role.label("admin")))

        // ID helper tests
        print(ID.unique())
        print(ID.custom("custom_id"))

        mock = try await general.headers()
        print(mock.result)
        } catch {
            print(error.localizedDescription)
        }
    }
}
