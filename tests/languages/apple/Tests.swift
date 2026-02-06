import XCTest
import Foundation
#if canImport(FoundationNetworking)
import FoundationNetworking
#endif
import Appwrite
import AppwriteEnums
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
            .setProject("123456")
            .addHeader(key: "Origin", value: "http://localhost")
            .setSelfSigned()

        // Ping pong test
        let ping = try await client.ping()
        let pingResult = parse(from: ping)!
        print(pingResult)

        // reset configs
        client.setProject("console")
        client.setEndpointRealtime("wss://stage.cloud.appwrite.io/v1")
        client.setSelfSigned(false)

        let foo = Foo(client)
        let bar = Bar(client)
        let general = General(client)
        let realtime = Realtime(client)
        var realtimeResponse = "Realtime failed!"
        var realtimeResponseWithQueries = "Realtime failed!"
        var realtimeResponseWithQueriesFailure = "Realtime failed!"

        let expectation = XCTestExpectation(description: "realtime server")
        let expectationWithQueries = XCTestExpectation(description: "realtime server (with queries)")
        let expectationWithQueriesFailure = XCTestExpectation(description: "realtime server (with queries failure)")
        expectationWithQueriesFailure.isInverted = true

        // Subscribe without queries
        try await realtime.subscribe(channels: ["tests"]) { message in
            realtimeResponse = message.payload!["response"] as! String
            expectation.fulfill()
        }

        // Subscribe with queries to ensure query array support works
        try await realtime.subscribe(
            channels: ["tests"],
            queries: [
                Query.equal("response", value: ["WS:/v1/realtime:passed"])
            ]
        ) { message in
            realtimeResponseWithQueries = message.payload?["response"] as! String
            expectationWithQueries.fulfill()
        }

        try await realtime.subscribe(
            channels: ["tests"],
            queries: [
                Query.equal("response", value: ["failed"])
            ]
        ) { message in
            realtimeResponseWithQueriesFailure = message.payload?["response"] as! String
            expectationWithQueriesFailure.fulfill()
        }

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

        // Request model tests
        mock = try await general.createPlayer(player: Player(id: "player1", name: "John Doe", score: 100))
        print(mock.result)

        mock = try await general.createPlayers(players: [
            Player(id: "player1", name: "John Doe", score: 100),
            Player(id: "player2", name: "Jane Doe", score: 200)
        ])
        print(mock.result)

        do {
            try await general.error400()
        } catch let error as AppwriteError {
            print(error.message)
            print(error.response)
        }

        do {
            try await general.error500()
        } catch let error as AppwriteError {
            print(error.message)
            print(error.response)
        }

        do {
            try await general.error502()
        } catch let error as AppwriteError {
            print(error.message)
            print(error.response)
        }

        print("Invalid endpoint URL: htp://cloud.appwrite.io/v1") // Indicates fatalError by client.setEndpoint

        wait(for: [expectation], timeout: 20.0)
        print(realtimeResponse)

        wait(for: [expectationWithQueries], timeout: 20.0)
        print(realtimeResponseWithQueries)
        
        wait(for: [expectationWithQueriesFailure], timeout: 20.0)
        if expectationWithQueriesFailure.isInverted {
            print(realtimeResponseWithQueriesFailure)
        } else {
            print("Realtime failed")
        }

        mock = try await general.setCookie()
        print(mock.result)

        mock = try await general.getCookie()
        print(mock.result)

        try! await general.empty()

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
        print(Query.orderRandom())
        print(Query.cursorAfter("my_movie_id"))
        print(Query.cursorBefore("my_movie_id"))
        print(Query.limit(50))
        print(Query.offset(20))
        print(Query.contains("title", value: "Spider"))
        print(Query.contains("labels", value: "first"))
        
        // New query methods
        print(Query.notContains("title", value: "Spider"))
        print(Query.notSearch("name", value: "john"))
        print(Query.notBetween("age", start: 50, end: 100))
        print(Query.notStartsWith("name", value: "Ann"))
        print(Query.notEndsWith("name", value: "nne"))
        print(Query.createdBefore("2023-01-01"))
        print(Query.createdAfter("2023-01-01"))
        print(Query.createdBetween("2023-01-01", "2023-12-31"))
        print(Query.updatedBefore("2023-01-01"))
        print(Query.updatedAfter("2023-01-01"))
        print(Query.updatedBetween("2023-01-01", "2023-12-31"))
        
        // Spatial Distance query tests
        print(Query.distanceEqual("location", values: [[40.7128, -74],[40.7128, -74]], distance: 1000))
        print(Query.distanceEqual("location", values: [40.7128, -74], distance: 1000, meters: true))
        print(Query.distanceNotEqual("location", values: [40.7128, -74], distance: 1000))
        print(Query.distanceNotEqual("location", values: [40.7128, -74], distance: 1000, meters: true))
        print(Query.distanceGreaterThan("location", values: [40.7128, -74], distance: 1000))
        print(Query.distanceGreaterThan("location", values: [40.7128, -74], distance: 1000, meters: true))
        print(Query.distanceLessThan("location", values: [40.7128, -74], distance: 1000))
        print(Query.distanceLessThan("location", values: [40.7128, -74], distance: 1000, meters: true))

        // Spatial query tests
        print(Query.intersects("location", values: [40.7128, -74]))
        print(Query.notIntersects("location", values: [40.7128, -74]))
        print(Query.crosses("location", values: [40.7128, -74]))
        print(Query.notCrosses("location", values: [40.7128, -74]))
        print(Query.overlaps("location", values: [40.7128, -74]))
        print(Query.notOverlaps("location", values: [40.7128, -74]))
        print(Query.touches("location", values: [40.7128, -74]))
        print(Query.notTouches("location", values: [40.7128, -74]))
        print(Query.contains("location", value: [[40.7128, -74],[40.7128, -74]]))
        print(Query.notContains("location", value: [[40.7128, -74],[40.7128, -74]]))
        print(Query.equal("location", value: [[40.7128, -74],[40.7128, -74]]))
        print(Query.notEqual("location", value: [[40.7128, -74],[40.7128, -74]]))
        
        print(Query.or([
            Query.equal("released", value: true),
            Query.lessThan("releasedYear", value: 1990)
        ]))
        print(Query.and([
            Query.equal("released", value: false),
            Query.greaterThan("releasedYear", value: 2015)
        ]))

        // regex, exists, notExists, elemMatch
        print(Query.regex("name", pattern: "pattern.*"))
        print(Query.exists(["attr1", "attr2"]))
        print(Query.notExists(["attr1", "attr2"]))
        print(Query.elemMatch("friends", queries: [
            Query.equal("name", value: "Alice"),
            Query.greaterThan("age", value: 18)
        ]))

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

        // Channel helper tests
        print(Channel.database().collection().document().toString())
        print(Channel.database("db1").collection("col1").document("doc1").toString())
        print(Channel.database("db1").collection("col1").document("doc1").create().toString())
        print(Channel.tablesdb().table().row().toString())
        print(Channel.tablesdb("db1").table("table1").row("row1").toString())
        print(Channel.tablesdb("db1").table("table1").row("row1").update().toString())
        print(Channel.account())
        print(Channel.account("user123"))
        print(Channel.bucket().file().toString())
        print(Channel.bucket("bucket1").file("file1").toString())
        print(Channel.bucket("bucket1").file("file1").delete().toString())
        print(Channel.function().execution().toString())
        print(Channel.function("func1").execution("exec1").toString())
        print(Channel.function("func1").execution("exec1").create().toString())
        print(Channel.team().toString())
        print(Channel.team("team1").toString())
        print(Channel.team("team1").create().toString())
        print(Channel.membership().toString())
        print(Channel.membership("membership1").toString())
        print(Channel.membership("membership1").update().toString())

        // Operator helper tests
        print(Operator.increment(1))
        print(Operator.increment(5, max: 100))
        print(Operator.decrement(1))
        print(Operator.decrement(3, min: 0))
        print(Operator.multiply(2))
        print(Operator.multiply(3, max: 1000))
        print(Operator.divide(2))
        print(Operator.divide(4, min: 1))
        print(Operator.modulo(5))
        print(Operator.power(2))
        print(Operator.power(3, max: 100))
        print(Operator.arrayAppend(["item1", "item2"]))
        print(Operator.arrayPrepend(["first", "second"]))
        print(Operator.arrayInsert(0, value: "newItem"))
        print(Operator.arrayRemove("oldItem"))
        print(Operator.arrayUnique())
        print(Operator.arrayIntersect(["a", "b", "c"]))
        print(Operator.arrayDiff(["x", "y"]))
        print(Operator.arrayFilter(Condition.equal, value: "test"))
        print(Operator.stringConcat("suffix"))
        print(Operator.stringReplace("old", "new"))
        print(Operator.toggle())
        print(Operator.dateAddDays(7))
        print(Operator.dateSubDays(3))
        print(Operator.dateSetNow())

        mock = try await general.headers()
        print(mock.result)
    }

    func parse(from json: String) -> String? {
        if let data = json.data(using: .utf8),
           let jsonObject = try? JSONSerialization.jsonObject(with: data, options: []) as? [String: Any],
           let result = jsonObject["result"] as? String {
            return result
        }
        return nil
    }
}
