import XCTest
import Foundation
#if canImport(FoundationNetworking)
import FoundationNetworking
#endif
import Appwrite
import JSONCodable
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
        let sdkHeaders = client.getHeaders()
        print("x-sdk-name: \(sdkHeaders["x-sdk-name"] ?? "nil"); x-sdk-platform: \(sdkHeaders["x-sdk-platform"] ?? "nil"); x-sdk-language: \(sdkHeaders["x-sdk-language"] ?? "nil"); x-sdk-version: \(sdkHeaders["x-sdk-version"] ?? "nil")")

        // Ping pong test
        let ping = try await client.ping()
        let pingResult = parse(from: ping)!
        print(pingResult)

        // reset configs
        client.setProject("console")
        client.setEndpointRealtime("ws://mockapi/v1")
        // Keep selfSigned=true so WebSocketClient skips TLS for the ws:// mock endpoint.

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

        // Watchdog: flipped to true if the callback fires after unsubscribe, so we can
        // verify the subscription is *actually* torn down, not just that the call
        // resolved without throwing.
        var rtsubFailureUnsubscribed = false
        var rtsubFailureFiredAfterUnsubscribe = false

        // Subscribe without queries
        let rtsub = try await realtime.subscribe(channels: ["tests"]) { message in
            realtimeResponse = message.payload!["response"] as! String
            expectation.fulfill()
        }

        // Subscribe with queries to ensure query array support works
        let rtsubWithQueries = try await realtime.subscribe(
            channels: ["tests"],
            queries: [
                Query.equal("response", value: ["WS:/v1/realtime:passed"])
            ]
        ) { message in
            realtimeResponseWithQueries = message.payload?["response"] as! String
            expectationWithQueries.fulfill()
        }

        let rtsubWithQueriesFailure = try await realtime.subscribe(
            channels: ["tests"],
            queries: [
                Query.equal("response", value: ["failed"])
            ]
        ) { message in
            if rtsubFailureUnsubscribed { rtsubFailureFiredAfterUnsubscribe = true }
            realtimeResponseWithQueriesFailure = message.payload?["response"] as! String
            expectationWithQueriesFailure.fulfill()
        }

        let rtPresenceSub = try await realtime.subscribe(channels: ["presences"]) { message in
            guard let payload = message.payload,
                  payload["$id"] as? String == "p-test" else { return }
            print("Realtime presence:passed")
        }

        var realtimeErrorResponse = "Realtime error:failed"
        let expectationError = XCTestExpectation(description: "realtime error")
        realtime.onError { _, _ in
            realtimeErrorResponse = "Realtime error:passed"
            expectationError.fulfill()
        }
        _ = try await realtime.subscribe(channels: ["error"]) { _ in }

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

        for (id, plain) in [("", "0"), ("0", "")] {
            do {
                _ = try await general.validatePath(plain: plain, id: id)
                XCTFail("Empty path parameter was accepted")
            } catch let error as AppwriteError {
                print(error.message)
            }
        }
        print(try await general.validatePath(plain: "0", id: "0").result)
        print(try await general.validatePath(plain: "0", id: nil).result)
        // The mock routes are registered at the encoded paths, so each call only reaches
        // its route if the ID is escaped as one segment.
        print(try await general.getPath(pathId: "grant/special&id").result)
        print(try await general.getPath(pathId: "../grant?special=id#x y%").result)
        print(try await general.getPath(pathId: "unique()").result)
        print(try await general.getQuery(value: "a&b+c#d=e%f", values: ["a&b", "c+d#e"]).result)

        let zone: String = try await Plaintext(client).getZone()
        print(zone)
        let mixed: Mock = try await general.getMixed()
        print(mixed.result)
        let plaintext = Plaintext(client)
        var imported: String = try await plaintext.importZone(records: "www 3600 IN A 192.0.2.1")
        print(imported)
        let zoneFile = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../../resources/file.png")
        imported = try await plaintext.importZone(records: "www 3600 IN A 192.0.2.1", zone: zoneFile)
        print(imported)

        let message = "conversation without a required file"
        mock = try await general.optionalUpload(message: message)
        print(mock.result)

        let attachment = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../../resources/file.png")
        mock = try await general.optionalUpload(message: message, attachment: attachment)
        print(mock.result)

        mock = try await general.optionalUpload(message: message, metadata: ["source": "sdk", "uri": "café"])
        print(mock.result)
        mock = try await general.optionalUpload(message: message, attachment: attachment, metadata: ["source": "sdk", "uri": "café"])
        print(mock.result)

        do {
            var file = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../../resources/file.png")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            var file = InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../../resources/large_file.mp4")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../../resources/file.png")
            var buffer = ByteBuffer(bytes: try! Data(contentsOf: url))
            var file = InputFile.fromBuffer(buffer, filename: "file.png", mimeType: "image/png")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            var url = URL(fileURLWithPath: "\(FileManager.default.currentDirectoryPath)/../../../resources/large_file.mp4")
            var buffer = ByteBuffer(bytes: try! Data(contentsOf: url))
            var file = InputFile.fromBuffer(buffer, filename: "large_file.mp4", mimeType: "video/mp4")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        do {
            let file = InputFile.fromData(Data(count: 5 * 1024 * 1024), filename: "boundary.bin", mimeType: "application/octet-stream")
            mock = try await general.upload(x: "string", y: 123, z: ["string in array"], file: file, onProgress: nil)
            print(mock.result)
        } catch {
            print(error.localizedDescription)
        }

        let profile = try await general.uploadGeneric(file: InputFile.fromPath("\(FileManager.default.currentDirectoryPath)/../../../resources/file.png"))
        print(profile.prefs.data["result"]?.value as? String ?? "")

        var downloaded = try await general.download()
        print(downloaded.readString(length: downloaded.readableBytes) ?? "")

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

        mock = try await general.createDocuments(documents: [
            AnyCodable(["$id": "one", "title": "hello"]),
            AnyCodable(["$id": "two", "title": "world"])
        ])
        print(mock.result)

        let preset = try await general.getPreset()
        print("preset: name=\(preset.name), count=\(preset.count), enabled=\(preset.enabled)")
        for item in try await general.listPresets().presets {
            print("preset: name=\(item.name), count=\(item.count), enabled=\(item.enabled)")
        }
        print("empty presets: \(try await general.listEmptyPresets().presets.count)")
        print("preset verified: \(preset.verified.map { String($0) } ?? "null")")
        print("preset scopes: \((preset.scopes ?? []).map { $0.rawValue }.joined(separator: ","))")

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

        await fulfillment(of: [expectation], timeout: 20.0)
        print(realtimeResponse)

        await fulfillment(of: [expectationWithQueries], timeout: 20.0)
        print(realtimeResponseWithQueries)

        await fulfillment(of: [expectationWithQueriesFailure], timeout: 20.0)
        if expectationWithQueriesFailure.isInverted {
            print(realtimeResponseWithQueriesFailure)
        } else {
            print("Realtime failed")
        }

        await fulfillment(of: [expectationError], timeout: 20.0)
        print(realtimeErrorResponse)

        do {
            try await rtsubWithQueriesFailure.unsubscribe()
            rtsubFailureUnsubscribed = true

            // Idempotence: a second unsubscribe on the same handle must not throw.
            try await rtsubWithQueriesFailure.unsubscribe()

            // Give any in-flight frames a chance to be dispatched to the callback.
            // If we're truly unsubscribed, the watchdog flag stays false.
            try await Task.sleep(nanoseconds: 500_000_000)

            if rtsubFailureFiredAfterUnsubscribe {
                throw NSError(domain: "RealtimeTest", code: 1, userInfo: [NSLocalizedDescriptionKey: "callback fired after unsubscribe"])
            }

            print("Realtime unsubscribe:passed")
        } catch {
            print("Realtime unsubscribe:failed")
        }

        do {
            try await rtsubWithQueries.update(.init(channels: ["tests"], queries: []))
            print("Realtime update:passed")
        } catch {
            print("Realtime update:failed")
        }

        // Fires the upsert. The "Realtime presence:passed" line is
        // printed by rtPresenceSub's callback when the fan-out event for
        // this presence document arrives — verifying the full round-trip
        // rather than just "no exception thrown".
        try realtime.upsertPresence(
            status: "online",
            presenceId: "p-test",
            metadata: ["page": "/home"]
        )
        // Give the server time to fan out and the cb to fire before we
        // tear the socket down with disconnect() below.
        try await Task.sleep(nanoseconds: 1_000_000_000)

        do {
            try await realtime.disconnect()
            print("Realtime disconnect:passed")
        } catch {
            print("Realtime disconnect:failed")
        }

        print(try await firstFrameAfterUpgrade())
        print(try await connectRejectsUpgrade())
        print(try await subscribeReusesSocket())
        print(try await realtimeReconnects())

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
        print(Query.containsAny("labels", value: ["first", "second"]))
        print(Query.containsAll("labels", value: ["first", "second"]))

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
        print(Query.vectorDot("embedding", vector: [0.1, 0.2, 0.3]))
        print(Query.vectorCosine("embedding", vector: [0.1, 0.2, 0.3]))
        print(Query.vectorEuclidean("embedding", vector: [0.1, 0.2, 0.3]))

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
        print(Query.relationship("comments", queries: [
            Query.equal("approved", value: true),
            Query.limit(2)
        ]))
        let transportQueries: [String] = [
            Query.count("*", alias: "total"),
            Query.join("orders", left: "$id", right: "customerId"),
            Query.groupBy(["status"]),
            Query.distinct(),
            Query.covers("location", values: [1, 2]),
            Query.countDistinct("year", alias: "uniqueYears"),
            Query.sum("price", alias: "total"),
            Query.avg("price", alias: "avgPrice"),
            Query.min("price", alias: "lowest"),
            Query.max("price", alias: "highest"),
            Query.stddev("price", alias: "sd"),
            Query.stddevPop("price", alias: "sdp"),
            Query.stddevSamp("price", alias: "sds"),
            Query.variance("price", alias: "var"),
            Query.varPop("price", alias: "vp"),
            Query.varSamp("price", alias: "vs"),
            Query.bitAnd("flags", alias: "band"),
            Query.bitOr("flags", alias: "bor"),
            Query.bitXor("flags", alias: "bxor"),
            Query.having([Query.greaterThan("total", value: 1)]),
            Query.leftJoin("orders", left: "$id", right: "customerId", operator: "=", alias: "ord"),
            Query.rightJoin("orders", left: "$id", right: "customerId"),
            Query.fullOuterJoin("orders", left: "$id", right: "customerId"),
            Query.crossJoin("orders", alias: "ord"),
            Query.on("$id", "customerId"),
            Query.leftJoin("orders", alias: "ord", on: [
                Query.on("$id", "customerId"),
                Query.equal("ord.status", value: "paid"),
            ]),
            Query.notCovers("location", values: [1, 2]),
            Query.spatialEquals("location", values: [1, 2]),
            Query.notSpatialEquals("location", values: [1, 2]),
        ]
        let queryTransport = try await general.listRows(
            queries: transportQueries + Query.page(2, perPage: 10) + Query.builder().limit(1).build()
        )
        print(queryTransport.result)

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

        // Topic helper tests
        print(try Topic.path(["user", "123", "notification"]).toString())
        print(try Topic.path(["org", "42", "user", "123"]).path(["notification"]).toString())
        let topicCases: [(String, [String])] = [
            ("empty path", []),
            ("empty level", ["user", ""]),
            ("slash", ["user/123"]),
            ("plus", ["user", "a+b"]),
            ("hash", ["user", "#"]),
            ("nul", ["user", "a\u{0}b"]),
        ]
        for (name, levels) in topicCases {
            do {
                _ = try Topic.path(levels)
                print("Topic \(name):failed")
            } catch {
                print("Topic \(name):passed")
            }
        }

        // Channel helper tests
        print(try Channel.database("db1").collection("col1").document().toString())
        print(try Channel.database("db1").collection("col1").document("doc1").toString())
        print(try Channel.database("db1").collection("col1").document("doc1").create().toString())
        print(try Channel.database("db1").collection("col1").document("doc1").upsert().toString())
        print(try Channel.tablesdb("db1").table("table1").row().toString())
        print(try Channel.tablesdb("db1").table("table1").row("row1").toString())
        print(try Channel.tablesdb("db1").table("table1").row("row1").update().toString())
        print(Channel.account())
        print(try Channel.bucket("bucket1").file().toString())
        print(try Channel.bucket("bucket1").file("file1").toString())
        print(try Channel.bucket("bucket1").file("file1").delete().toString())
        print(try Channel.function("func2").toString())
        print(try Channel.function("func1").toString())
        print(try Channel.execution("exec2").toString())
        print(try Channel.execution("exec1").toString())
        print(Channel.documents())
        print(Channel.rows())
        print(Channel.files())
        print(Channel.executions())
        print(Channel.teams())
        print(try Channel.team("team2").toString())
        print(try Channel.team("team1").toString())
        print(try Channel.team("team1").create().toString())
        print(Channel.memberships())
        print(try Channel.membership("membership2").toString())
        print(try Channel.membership("membership1").toString())
        print(try Channel.membership("membership1").update().toString())
        print(Channel.presences())
        print(try Channel.presence("presence2").toString())
        print(try Channel.presence("presence1").toString())
        print(try Channel.presence("presence1").upsert().toString())
        print(try Channel.presence("presence1").update().toString())
        print(try Channel.presence("presence1").delete().toString())

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

        // Native push (MQTT) round-trip against the mock broker.
        _ = client.setJWT("eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VySWQiOiJlMmUtdXNlciJ9.e2e")
        _ = client.setPushEndpoint("mqtt://mqtt:1883")
        let push = Push(client)
        let pushOpenExpectation = XCTestExpectation(description: "push open")
        _ = push.onOpen { pushOpenExpectation.fulfill() }
        let pushExpectation = XCTestExpectation(description: "push message")
        var pushBody = "Push message:failed"
        var pushQos = "Push qos:failed"
        let pushSub = try await push.subscribe([Topic.path(["e2e-push"])]) { message in
            if message.data == "push-payload" && message.topic == "e2e-push" {
                pushBody = "Push message:passed"
            }
            if message.qos == 1 {
                pushQos = "Push qos:passed"
            }
            pushExpectation.fulfill()
        }
        print("Push subscribe:passed")
        // onOpen fires during subscribe (the connection is established there), so by now the
        // expectation is already fulfilled and this returns immediately.
        let pushOpenResult = XCTWaiter().wait(for: [pushOpenExpectation], timeout: 10)
        print(pushOpenResult == .completed ? "Push open:passed" : "Push open:failed")
        await fulfillment(of: [pushExpectation], timeout: 10)
        print(pushBody)
        // reliableDelivery (default) => QoS 1 end to end.
        print(pushQos)
        pushSub.unsubscribe()
        push.close()

        // Topic-less subscribe: the signed-in user's own topic, users/<userId>. After each
        // SUBSCRIBE the mock publishes to users/e2e-user, users/e2e-session-user and
        // users/other-user, so a client passes only if it receives its own topic and nothing
        // else (an over-broad users/+ or users/# subscription would also get the others).
        let e2eSession = "eyJpZCI6ImUyZS1zZXNzaW9uLXVzZXIiLCJzZWNyZXQiOiJlMmUtc2VjcmV0In0="
        func userTopicsOf(_ userClient: Client) async throws -> [String] {
            let userPush = Push(userClient)
            let received = TopicCollector()
            // The topic-less form defaults to background: true; the test skips notifications.
            let userPushSub = try await userPush.subscribe(background: false) { message in
                received.append(message.topic)
            }
            try await Task.sleep(nanoseconds: 3_000_000_000)
            userPushSub.unsubscribe()
            userPush.close()
            return received.topics
        }
        func onlyTopic(_ received: [String], _ expected: String) -> Bool {
            !received.isEmpty && received.allSatisfy { $0 == expected }
        }

        // JWT and session both set: the JWT's user wins.
        _ = client.setSession(e2eSession)
        let jwtTopics = (try? await userTopicsOf(client)) ?? []
        print(onlyTopic(jwtTopics, "users/e2e-user") ? "Push user topic:passed" : "Push user topic:failed")

        // Session only: the user id comes from the session secret.
        let sessionClient = Client()
            .setProject("console")
            .setSelfSigned()
            .setPushEndpoint("mqtt://mqtt:1883")
            .setSession(e2eSession)
        let sessionTopics = (try? await userTopicsOf(sessionClient)) ?? []
        print(onlyTopic(sessionTopics, "users/e2e-session-user") ? "Push user session topic:passed" : "Push user session topic:failed")

        // No credential: a topic-less subscribe has no user to resolve and throws.
        let anonymousPush = Push(
            Client()
                .setProject("console")
                .setSelfSigned()
                .setPushEndpoint("mqtt://mqtt:1883")
        )
        var noCredentialRejected = false
        do {
            _ = try await anonymousPush.subscribe { _ in }
        } catch let error as AppwriteError {
            // The credential error itself, not any failure (setup, connection, ...).
            noCredentialRejected = error.message.contains("signed-in user")
        } catch {
            noCredentialRejected = false
        }
        anonymousPush.close()
        print(noCredentialRejected ? "Push user no credential:passed" : "Push user no credential:failed")

        // Signed in through the client: with no JWT or session set on it, the user comes from the
        // session its sign-in saved with the client's cookies.
        let signInClient = Client()
            .setProject("console")
            .setSelfSigned()
            .setPushEndpoint("mqtt://mqtt:1883")
        let signInHost = URL(string: signInClient.endPoint)!.host!
        let encodedSession = e2eSession.addingPercentEncoding(withAllowedCharacters: .alphanumerics) ?? e2eSession
        UserDefaults.standard.set(["a_session_console=\(encodedSession); Path=/; HttpOnly"], forKey: signInHost)
        let signInTopics = (try? await userTopicsOf(signInClient)) ?? []
        UserDefaults.standard.removeObject(forKey: signInHost)
        print(onlyTopic(signInTopics, "users/e2e-session-user") ? "Push user sign-in session topic:passed" : "Push user sign-in session topic:failed")

        // Broker errors reach onError: a refused CONNECT (the mock refuses a "deny:<reason>" credential with <reason>)
        // and a server-initiated DISCONNECT (the mock disconnects clients that subscribe to
        // "e2e-disconnect/<reason>" with <reason>). MQTTNIO does not expose the broker's reason string, so Apple checks
        // that an error arrives, not its text.
        func firstError(_ errorPush: Push, subscribingTo topic: String) async -> String {
            let collector = ErrorCollector()
            _ = errorPush.onError { error in
                collector.record((error as? AppwriteError)?.message ?? error.localizedDescription)
            }
            _ = try? await errorPush.subscribe(topic) { _ in }
            for _ in 0..<50 where collector.message == nil {
                try? await Task.sleep(nanoseconds: 100_000_000)
            }
            errorPush.close()
            return collector.message ?? ""
        }

        let deniedPush = Push(
            Client()
                .setProject("console")
                .setSelfSigned()
                .setPushEndpoint("mqtt://mqtt:1883")
                .setJWT("deny:refused-by-test")
        )
        let deniedError = await firstError(deniedPush, subscribingTo: "e2e-push")
        print(!deniedError.isEmpty ? "Push connect error:passed" : "Push connect error:failed")

        let kickedError = await firstError(Push(client), subscribingTo: "e2e-disconnect/kicked-by-test")
        print(!kickedError.isEmpty ? "Push disconnect error:passed" : "Push disconnect error:failed")

        // With no onError, the broker's DISCONNECT is logged to stderr, which must stay out of
        // the stdout this harness compares.
        let unhandledPush = Push(client)
        let unhandledClosed = XCTestExpectation(description: "push closed without onError")
        _ = unhandledPush.onClose { unhandledClosed.fulfill() }
        _ = try? await unhandledPush.subscribe("e2e-disconnect/unhandled") { _ in }
        let unhandledResult = await XCTWaiter().fulfillment(of: [unhandledClosed], timeout: 10)
        unhandledPush.close()
        print(unhandledResult == .completed ? "Push disconnect without onError:passed" : "Push disconnect without onError:failed")

        func outcome(_ subscribing: () async throws -> Void) async -> String {
            do {
                try await subscribing()
                return ""
            } catch {
                let message = (error as? AppwriteError)?.message ?? error.localizedDescription
                return message.isEmpty ? "failed" : message
            }
        }

        let deniedSwitch = await outcome {
            _ = try await Push(
                Client()
                    .setProject("console")
                    .setSelfSigned()
                    .setPushEndpoint("mqtt://mqtt:1883")
                    .setJWT("deny:switched-user")
            ).subscribe("e2e-switch") { _ in }
        }

        let switchClient = Client()
            .setProject("console")
            .setSelfSigned()
            .setPushEndpoint("mqtt://mqtt:1883")
            .setSession(e2eSession)
        let switchPush = Push(switchClient)
        let initial = await outcome { _ = try await switchPush.subscribe("e2e-switch") { _ in } }
        _ = switchClient.setJWT("deny:switched-user")
        let switched = await outcome { _ = try await switchPush.subscribe("e2e-switch") { _ in } }
        switchPush.close()
        print(
            initial.isEmpty && !deniedSwitch.isEmpty && switched == deniedSwitch
                ? "Push credential switch:passed"
                : "Push credential switch:failed (initial: \(initial), switched: \(switched), denied: \(deniedSwitch))"
        )

        let pendingClient = Client()
            .setProject("console")
            .setSelfSigned()
            .setPushEndpoint("mqtt://mqtt:1883")
            .setJWT("slow:pending")
        let pendingPush = Push(pendingClient)
        let firstPending = ErrorCollector()
        Task {
            firstPending.record(await outcome { _ = try await pendingPush.subscribe("e2e-switch") { _ in } })
        }
        try? await Task.sleep(nanoseconds: 100_000_000)
        _ = pendingClient.setJWT("deny:switched-user")
        let secondPending = await outcome { _ = try await pendingPush.subscribe("e2e-switch") { _ in } }
        for _ in 0..<100 where firstPending.message == nil {
            try? await Task.sleep(nanoseconds: 100_000_000)
        }
        let firstPendingOutcome = firstPending.message ?? "timeout"
        pendingPush.close()
        print(
            secondPending == deniedSwitch && firstPendingOutcome == deniedSwitch
                ? "Push credential pending switch:passed"
                : "Push credential pending switch:failed (first: \(firstPendingOutcome), second: \(secondPending))"
        )

        let closingPush = Push(
            Client()
                .setProject("console")
                .setSelfSigned()
                .setPushEndpoint("mqtt://mqtt:1883")
                .setJWT("slow:closing")
        )
        let closedWhileConnecting = ErrorCollector()
        Task {
            closedWhileConnecting.record(await outcome { _ = try await closingPush.subscribe("e2e-switch") { _ in } })
        }
        try? await Task.sleep(nanoseconds: 100_000_000)
        closingPush.close()
        for _ in 0..<100 where closedWhileConnecting.message == nil {
            try? await Task.sleep(nanoseconds: 100_000_000)
        }
        let closedOutcome = closedWhileConnecting.message ?? "timeout"
        print(
            closedOutcome == "Push was closed before the subscription was established"
                ? "Push close while connecting:passed"
                : "Push close while connecting:failed (\(closedOutcome))"
        )

        let callbackPush = Push(
            Client()
                .setProject("console")
                .setSelfSigned()
                .setPushEndpoint("mqtt://mqtt:1883")
                .setSession(e2eSession)
        )
        // Dropping the last subscription closes the connection while its UNSUBSCRIBE is in flight,
        // and reopening at once clears `closing` before that UNSUBSCRIBE fails. The failure is the
        // closed connection's teardown, so it must not reach onError (or stderr without one).
        let callbackErrors = ErrorCollector()
        _ = callbackPush.onError { error in
            callbackErrors.record((error as? AppwriteError)?.message ?? "\(error)")
        }
        let handle = SubscriptionBox()
        let closedFromCallback = ErrorCollector()
        let reopenedFromCallback = ErrorCollector()
        handle.subscription = try? await callbackPush.subscribe("e2e-push") { _ in
            handle.subscription?.unsubscribe()
            closedFromCallback.record("closed")
            Task {
                reopenedFromCallback.record(await outcome { _ = try await callbackPush.subscribe("e2e-switch") { _ in } })
            }
        }
        for _ in 0..<50 where reopenedFromCallback.message == nil {
            try? await Task.sleep(nanoseconds: 100_000_000)
        }
        try? await Task.sleep(nanoseconds: 1_000_000_000)
        let reopened = reopenedFromCallback.message ?? "timeout"
        callbackPush.close()
        print(
            closedFromCallback.message == "closed" && reopened.isEmpty && callbackErrors.message == nil
                ? "Push close from callback:passed"
                : "Push close from callback:failed (callback: \(closedFromCallback.message ?? "none"), reopened: \(reopened), error: \(callbackErrors.message ?? "none"))"
        )
    }

    /// A server may write its first frame in the same packet as the 101 response.
    func firstFrameAfterUpgrade() async throws -> String {
        let group = MultiThreadedEventLoopGroup(numberOfThreads: 1)
        defer { try? group.syncShutdownGracefully() }
        let server = try await ScriptedUpgrade.serve(on: group) { _ in
            ScriptedUpgrade(.accept(frames: [ScriptedUpgrade.firstFrame]))
        }
        let received = XCTestExpectation(description: "first frame")
        let socket = WebSocketClient("ws://127.0.0.1:\(server.localAddress!.port!)/v1/realtime", tlsEnabled: false)!
        socket.onMessage { (text: String) in
            if text == ScriptedUpgrade.firstFrame {
                received.fulfill()
            }
        }
        try await socket.connect()
        let result = await XCTWaiter().fulfillment(of: [received], timeout: 5)
        socket.close()
        try await server.close()
        return result == .completed ? "WebSocket first frame:passed" : "WebSocket first frame:failed"
    }

    /// connect() must throw when the server answers the upgrade with anything but 101, mapping known statuses.
    func connectRejectsUpgrade() async throws -> String {
        let group = MultiThreadedEventLoopGroup(numberOfThreads: 1)
        defer { try? group.syncShutdownGracefully() }
        var outcomes: [String] = []
        for status in ["404 Not Found", "403 Forbidden"] {
            let server = try await ScriptedUpgrade.serve(on: group) { _ in
                ScriptedUpgrade(.reject(status: status))
            }
            let socket = WebSocketClient("ws://127.0.0.1:\(server.localAddress!.port!)/v1/realtime", tlsEnabled: false)!
            do {
                try await socket.connect()
                outcomes.append("connected")
            } catch {
                outcomes.append(String(describing: error))
            }
            try await server.close()
        }
        return outcomes == ["notFound", "upgradeRejected"]
            ? "WebSocket upgrade rejection:passed"
            : "WebSocket upgrade rejection:failed (\(outcomes.joined(separator: ", ")))"
    }

    /// A subscribe made right after another has returned must reuse that subscribe's socket, even when
    /// the server is slow to accept the upgrade.
    func subscribeReusesSocket() async throws -> String {
        let group = MultiThreadedEventLoopGroup(numberOfThreads: 1)
        defer { try? group.syncShutdownGracefully() }
        let connections = ConnectionCounter()
        let server = try await ScriptedUpgrade.serve(on: group) { _ in
            connections.increment()
            return ScriptedUpgrade(.accept(frames: [ScriptedUpgrade.connected]), after: .milliseconds(300))
        }
        let realtime = Realtime(scriptedRealtimeClient(port: server.localAddress!.port!))
        _ = try await realtime.subscribe(channel: "tests") { _ in }
        _ = try await realtime.subscribe(channel: "files") { _ in }
        try await Task.sleep(nanoseconds: 500_000_000)
        try await realtime.disconnect()
        try await server.close()
        let count = connections.count
        return count == 1 ? "Realtime subscribe reuses socket:passed" : "Realtime subscribe reuses socket:failed (\(count) connections)"
    }

    /// Realtime must reconnect every time the server drops it, including after a reconnect attempt fails.
    func realtimeReconnects() async throws -> String {
        let group = MultiThreadedEventLoopGroup(numberOfThreads: 1)
        defer { try? group.syncShutdownGracefully() }
        let replies: [ScriptedUpgrade.Reply] = [
            .accept(frames: [ScriptedUpgrade.connected], close: true),
            .drop,
            .accept(frames: [ScriptedUpgrade.connected], close: true),
            .accept(frames: [ScriptedUpgrade.connected]),
        ]
        let connections = ConnectionCounter()
        let server = try await ScriptedUpgrade.serve(on: group) { _ in
            ScriptedUpgrade(replies[min(connections.increment(), replies.count) - 1])
        }
        let realtime = Realtime(scriptedRealtimeClient(port: server.localAddress!.port!))
        let opened = XCTestExpectation(description: "realtime opened three times")
        opened.expectedFulfillmentCount = 3
        realtime.onOpen { opened.fulfill() }
        _ = try await realtime.subscribe(channel: "tests") { _ in }
        let result = await XCTWaiter().fulfillment(of: [opened], timeout: 15)
        try await realtime.disconnect()
        try await server.close()
        return result == .completed ? "Realtime reconnect:passed" : "Realtime reconnect:failed (\(connections.count) connections)"
    }

    func scriptedRealtimeClient(port: Int) -> Client {
        return Client()
            .setProject("123456")
            .setSelfSigned()
            .setEndpointRealtime("ws://127.0.0.1:\(port)/v1")
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

/// Answers the WebSocket upgrade request from raw bytes, so a test controls whether and when the upgrade
/// is accepted, the frames sent with it and whether the server then closes the socket.
final class ScriptedUpgrade: ChannelInboundHandler, @unchecked Sendable {
    typealias InboundIn = ByteBuffer
    typealias OutboundOut = ByteBuffer

    enum Reply {
        case accept(frames: [String], close: Bool = false)
        case reject(status: String)
        case drop
    }

    static let firstFrame = "first-frame"
    static let connected = #"{"type":"connected","data":{"channels":[],"user":null}}"#
    // Sec-WebSocket-Accept for the fixed Sec-WebSocket-Key WebSocketClient sends.
    private static let accept = "ZXBe/0Qtt2IZI9ybavBlj+XPte8="

    private let reply: Reply
    private let delay: TimeAmount
    private var request = ""
    private var answered = false

    init(_ reply: Reply, after delay: TimeAmount = .zero) {
        self.reply = reply
        self.delay = delay
    }

    static func serve(
        on group: EventLoopGroup,
        handler: @escaping @Sendable (NIOCore.Channel) -> ScriptedUpgrade
    ) async throws -> NIOCore.Channel {
        return try await ServerBootstrap(group: group)
            .childChannelInitializer { $0.pipeline.addHandler(handler($0)) }
            .bind(host: "127.0.0.1", port: 0)
            .get()
    }

    func channelRead(context: ChannelHandlerContext, data: NIOAny) {
        guard !answered else {
            return
        }
        var buffer = unwrapInboundIn(data)
        request += buffer.readString(length: buffer.readableBytes) ?? ""
        guard request.hasSuffix("\r\n\r\n") else {
            return
        }
        answered = true
        if delay == .zero {
            answer(context: context)
        } else {
            context.eventLoop.scheduleTask(in: delay) { self.answer(context: context) }
        }
    }

    private func answer(context: ChannelHandlerContext) {
        switch reply {
        case .drop:
            context.close(promise: nil)
        case .reject(let status):
            let response = context.channel.allocator.buffer(string: "HTTP/1.1 \(status)\r\nContent-Length: 0\r\n\r\n")
            context.writeAndFlush(wrapOutboundOut(response), promise: nil)
        case .accept(let frames, let close):
            var response = context.channel.allocator.buffer(
                string: "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: \(Self.accept)\r\n\r\n"
            )
            for frame in frames {
                precondition(frame.utf8.count < 126, "frames use the single-byte payload length")
                response.writeBytes([0x81, UInt8(frame.utf8.count)])
                response.writeString(frame)
            }
            context.writeAndFlush(wrapOutboundOut(response), promise: nil)
            if close {
                context.eventLoop.scheduleTask(in: .milliseconds(100)) {
                    let closeFrame = context.channel.allocator.buffer(bytes: [0x88, 0x00])
                    context.writeAndFlush(self.wrapOutboundOut(closeFrame), promise: nil)
                }
            }
        }
    }
}

/// Counts the connections a scripted server accepts; NIO accepts them on its own thread.
final class ConnectionCounter: @unchecked Sendable {
    private let lock = NSLock()
    private var value = 0

    @discardableResult
    func increment() -> Int {
        lock.lock()
        defer { lock.unlock() }
        value += 1
        return value
    }

    var count: Int {
        lock.lock()
        defer { lock.unlock() }
        return value
    }
}

/// Collects received push topics; the push callback may run on another thread.
final class TopicCollector: @unchecked Sendable {
    private let lock = NSLock()
    private var received: [String] = []

    func append(_ topic: String) {
        lock.lock()
        defer { lock.unlock() }
        received.append(topic)
    }

    var topics: [String] {
        lock.lock()
        defer { lock.unlock() }
        return received
    }
}

final class SubscriptionBox: @unchecked Sendable {
    var subscription: PushSubscription?
}

final class ErrorCollector: @unchecked Sendable {
    private let lock = NSLock()
    private var first: String?

    var message: String? {
        lock.lock()
        defer { lock.unlock() }
        return first
    }

    func record(_ message: String) {
        lock.lock()
        defer { lock.unlock() }
        if first == nil {
            first = message
        }
    }
}
