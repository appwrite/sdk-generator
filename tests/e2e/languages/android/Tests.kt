package io.appwrite

import androidx.test.core.app.ApplicationProvider
import androidx.test.ext.junit.runners.AndroidJUnit4
import com.google.gson.Gson
import io.appwrite.exceptions.AppwriteException
import io.appwrite.Permission
import io.appwrite.Role
import io.appwrite.ID
import io.appwrite.Channel
import io.appwrite.Topic
import io.appwrite.Query
import io.appwrite.Operator
import io.appwrite.Condition
import io.appwrite.enums.MockType
import io.appwrite.extensions.fromJson
import io.appwrite.models.Error
import io.appwrite.models.InputFile
import io.appwrite.models.Mock
import io.appwrite.models.Player
import io.appwrite.models.RealtimeSubscriptionUpdate
import io.appwrite.services.Bar
import io.appwrite.services.Foo
import io.appwrite.services.General
import io.appwrite.services.Push
import io.appwrite.services.Realtime
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.delay
import kotlinx.coroutines.runBlocking
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.setMain
import okhttp3.MultipartBody
import okhttp3.Response
import okio.Buffer
import org.junit.After
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Before
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.annotation.Config
import java.io.File
import java.io.IOException
import java.nio.file.Files
import java.nio.file.Paths

data class TestPayload(val response: String)

// The app's PushReceiver in the background delivery test: records what reaches it and lets the
// SDK post its notification too.
class E2EPushReceiver : io.appwrite.services.PushReceiver() {
    companion object {
        val messages = java.util.concurrent.CopyOnWriteArrayList<String>()
    }

    override fun onMessage(context: android.content.Context, message: io.appwrite.services.PushMessage): Boolean {
        messages.add(message.data)
        return false
    }
}

@Config(manifest=Config.NONE)
@RunWith(AndroidJUnit4::class)
class ServiceTest {

    private val filename: String = "result.txt"

    @Before
    @ExperimentalCoroutinesApi
    fun setUp() {
        Dispatchers.setMain(Dispatchers.Unconfined)
        Files.deleteIfExists(Paths.get(filename))
        writeToFile("Test Started")
    }

    @After
    @ExperimentalCoroutinesApi
    fun tearDown() {
        Dispatchers.resetMain()
    }

    @Test
    @Throws(IOException::class)
    fun test() {
        val client = Client(ApplicationProvider.getApplicationContext())
            .setProject("123456")
            .addHeader("Origin", "http://localhost")
            .setSelfSigned(true)
        val sdkHeaders = client.getHeaders()

        writeToFile("x-sdk-name: ${sdkHeaders["x-sdk-name"]}; x-sdk-platform: ${sdkHeaders["x-sdk-platform"]}; x-sdk-language: ${sdkHeaders["x-sdk-language"]}; x-sdk-version: ${sdkHeaders["x-sdk-version"]}")

        runBlocking {
            val ping = client.ping()
            val pingResponse = parse(ping)
            writeToFile(pingResponse)
        }

        // reset configs
        client.setProject("console")
            .setEndpointRealtime("ws://mockapi/v1")

        val foo = Foo(client)
        val bar = Bar(client)
        val general = General(client)
        val realtime = Realtime(client)
        var realtimeResponse = "Realtime failed!"
        var realtimeResponseWithQueries = "Realtime failed!"
        var realtimeResponseWithQueriesFailure = "Realtime failed!"

        // Watchdog: flipped to true if the callback fires after unsubscribe, so we can
        // verify the subscription is *actually* torn down, not just that the call
        // resolved without throwing.
        var rtsubFailureUnsubscribed = false
        var rtsubFailureFiredAfterUnsubscribe = false

        // Subscribe without queries
        val rtsub = realtime.subscribe("tests", payloadType = TestPayload::class.java) {
            realtimeResponse = it.payload.response
        }

        // Subscribe with queries to ensure query set support works
        val rtsubWithQueries = realtime.subscribe(
            "tests",
            payloadType = TestPayload::class.java,
            queries = setOf(
                Query.equal("response", listOf("WS:/v1/realtime:passed"))
            )
        ) {
            realtimeResponseWithQueries = it.payload.response
        }

        val rtsubWithQueriesFailure = realtime.subscribe(
            "tests",
            payloadType = TestPayload::class.java,
            queries = setOf(
                Query.equal("response", listOf("failed"))
            )
        ) {
            if (rtsubFailureUnsubscribed) rtsubFailureFiredAfterUnsubscribe = true
            realtimeResponseWithQueriesFailure = "WS:/v1/realtime:passed"
        }

        val rtPresenceSub = realtime.subscribe(
            "presences",
            payloadType = Any::class.java
        ) { event ->
            @Suppress("UNCHECKED_CAST")
            val payload = event.payload as? Map<String, Any> ?: return@subscribe
            if (payload["\$id"] == "p-test") {
                writeToFile("Realtime presence:passed")
            }
        }

        var realtimeErrorResponse = "Realtime error:failed"
        realtime.onError { _, _ ->
            realtimeErrorResponse = "Realtime error:passed"
        }
        realtime.subscribe("error", payloadType = Any::class.java) { }

        runBlocking {
            var mock: Mock

            val nullableMockMap = Mock.from(mapOf("result" to "Success")).toMap()
            check(nullableMockMap.keys.containsAll(setOf("optionalResult", "status", "relatedMock")))
            check(nullableMockMap["optionalResult"] == null)
            check(nullableMockMap["status"] == null)
            check(nullableMockMap["relatedMock"] == null)

            // Foo Tests
            mock = foo.get("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = foo.post("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = foo.put("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = foo.patch("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = foo.delete("string", 123, listOf("string in array"))
            writeToFile(mock.result)

            // Bar Tests
            mock = bar.get("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = bar.post("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = bar.put("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = bar.patch("string", 123, listOf("string in array"))
            writeToFile(mock.result)
            mock = bar.delete("string", 123, listOf("string in array"))
            writeToFile(mock.result)

            // General Tests
            val result = general.redirect()
            writeToFile((result as Map<String, Any>)["result"] as String)

            for ((id, plain) in listOf("" to "0", "0" to "")) {
                try {
                    general.validatePath(plain, id)
                    error("Empty path parameter was accepted")
                } catch (e: AppwriteException) {
                    writeToFile(e.message ?: "Missing exception message")
                }
            }
            writeToFile(general.validatePath("0", "0").result)
            writeToFile(general.validatePath("0", null).result)

            try {
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromPath("../../../../resources/file.png"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            try {
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromPath("../../../../resources/large_file.mp4"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            try {
                var bytes = File("../../../../resources/file.png").readBytes()
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromBytes(bytes, "file.png", "image/png"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            try {
                var bytes = File("../../../../resources/large_file.mp4").readBytes()
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            writeToFile(String(general.download()))

            mock = general.enum(MockType.FIRST)
            writeToFile(mock.result)

            // Request model tests
            mock = general.createPlayer(Player(id = "player1", name = "John Doe", score = 100))
            writeToFile(mock.result)

            mock = general.createPlayers(listOf(
                Player(id = "player1", name = "John Doe", score = 100),
                Player(id = "player2", name = "Jane Doe", score = 200)
            ))
            writeToFile(mock.result)

            try {
                general.error400()
            } catch (e: AppwriteException) {
                writeToFile(e.message)
                writeToFile(e.response)
            }

            try {
                general.error500()
            } catch (e: AppwriteException) {
                writeToFile(e.message)
                writeToFile(e.response)
            }

            try {
                general.error502()
            } catch (e: AppwriteException) {
                writeToFile(e.message)
                writeToFile(e.response)
            }

            try {
                client.setEndpoint("htp://cloud.appwrite.io/v1")
            } catch (e: IllegalArgumentException) {
                writeToFile(e.message)
            }

            delay(30000)

            writeToFile(realtimeResponse)
            writeToFile(realtimeResponseWithQueries)
            writeToFile(realtimeResponseWithQueriesFailure)
            writeToFile(realtimeErrorResponse)

            try {
                rtsubWithQueriesFailure.unsubscribe()
                rtsubFailureUnsubscribed = true

                // Idempotence: a second unsubscribe on the same handle must not throw.
                rtsubWithQueriesFailure.unsubscribe()

                // Give any in-flight frames a chance to be dispatched to the callback.
                // If we're truly unsubscribed, the watchdog flag stays false.
                delay(500)

                if (rtsubFailureFiredAfterUnsubscribe) {
                    throw Exception("callback fired after unsubscribe")
                }

                writeToFile("Realtime unsubscribe:passed")
            } catch (e: Exception) {
                writeToFile("Realtime unsubscribe:failed")
            }

            try {
                rtsubWithQueries.update(
                    RealtimeSubscriptionUpdate(
                        channels = listOf("tests"),
                        queries = emptyList()
                    )
                )
                writeToFile("Realtime update:passed")
            } catch (e: Exception) {
                writeToFile("Realtime update:failed")
            }

            // Fires the upsert. The "Realtime presence:passed" line is
            // printed by rtPresenceSub's callback when the fan-out event
            // for this presence document arrives — verifying the full
            // round-trip rather than just "no exception thrown".
            realtime.upsertPresence(
                status = "online",
                presenceId = "p-test",
                metadata = mapOf("page" to "/home"),
            )
            // Give the server time to fan out and the cb to fire before
            // we tear the socket down with disconnect() below.
            delay(1000)

            try {
                realtime.disconnect()
                writeToFile("Realtime disconnect:passed")
            } catch (e: Exception) {
                writeToFile("Realtime disconnect:failed")
            }

            // mock = general.setCookie()
            // writeToFile(mock.result)

            // mock = general.getCookie()
            // writeToFile(mock.result)

            general.empty()

            // Query helper tests
            writeToFile(Query.equal("released", listOf(true)))
            writeToFile(Query.equal("title", listOf("Spiderman", "Dr. Strange")))
            writeToFile(Query.notEqual("title", "Spiderman"))
            writeToFile(Query.lessThan("releasedYear", 1990))
            writeToFile(Query.greaterThan("releasedYear", 1990))
            writeToFile(Query.search("name", "john"))
            writeToFile(Query.isNull("name"))
            writeToFile(Query.isNotNull("name"))
            writeToFile(Query.between("age", 50, 100))
            writeToFile(Query.between("age", 50.5, 100.5))
            writeToFile(Query.between("name", "Anna", "Brad"))
            writeToFile(Query.startsWith("name", "Ann"))
            writeToFile(Query.endsWith("name", "nne"))
            writeToFile(Query.select(listOf("name", "age")))
            writeToFile(Query.orderAsc("title"))
            writeToFile(Query.orderDesc("title"))
            writeToFile(Query.orderRandom())
            writeToFile(Query.cursorAfter("my_movie_id"))
            writeToFile(Query.cursorBefore("my_movie_id"))
            writeToFile(Query.limit(50))
            writeToFile(Query.offset(20))
            writeToFile(Query.contains("title", listOf("Spider")))
            writeToFile(Query.contains("labels", listOf("first")))
            writeToFile(Query.containsAny("labels", listOf("first", "second")))
            writeToFile(Query.containsAll("labels", listOf("first", "second")))

            // New query methods
            writeToFile(Query.notContains("title", listOf("Spider")))
            writeToFile(Query.notSearch("name", "john"))
            writeToFile(Query.notBetween("age", 50, 100))
            writeToFile(Query.notStartsWith("name", "Ann"))
            writeToFile(Query.notEndsWith("name", "nne"))
            writeToFile(Query.createdBefore("2023-01-01"))
            writeToFile(Query.createdAfter("2023-01-01"))
            writeToFile(Query.createdBetween("2023-01-01", "2023-12-31"))
            writeToFile(Query.updatedBefore("2023-01-01"))
            writeToFile(Query.updatedAfter("2023-01-01"))
            writeToFile(Query.updatedBetween("2023-01-01", "2023-12-31"))

            // Spatial Distance query tests
            writeToFile(Query.distanceEqual("location", listOf(listOf(40.7128, -74), listOf(40.7128, -74)), 1000))
            writeToFile(Query.distanceEqual("location", listOf(40.7128, -74), 1000, true))
            writeToFile(Query.distanceNotEqual("location", listOf(40.7128, -74), 1000))
            writeToFile(Query.distanceNotEqual("location", listOf(40.7128, -74), 1000, true))
            writeToFile(Query.distanceGreaterThan("location", listOf(40.7128, -74), 1000))
            writeToFile(Query.distanceGreaterThan("location", listOf(40.7128, -74), 1000, true))
            writeToFile(Query.distanceLessThan("location", listOf(40.7128, -74), 1000))
            writeToFile(Query.distanceLessThan("location", listOf(40.7128, -74), 1000, true))
            writeToFile(Query.vectorDot("embedding", listOf(0.1, 0.2, 0.3)))
            writeToFile(Query.vectorCosine("embedding", listOf(0.1, 0.2, 0.3)))
            writeToFile(Query.vectorEuclidean("embedding", listOf(0.1, 0.2, 0.3)))

            // Spatial query tests
            writeToFile(Query.intersects("location", listOf(40.7128, -74)))
            writeToFile(Query.notIntersects("location", listOf(40.7128, -74)))
            writeToFile(Query.crosses("location", listOf(40.7128, -74)))
            writeToFile(Query.notCrosses("location", listOf(40.7128, -74)))
            writeToFile(Query.overlaps("location", listOf(40.7128, -74)))
            writeToFile(Query.notOverlaps("location", listOf(40.7128, -74)))
            writeToFile(Query.touches("location", listOf(40.7128, -74)))
            writeToFile(Query.notTouches("location", listOf(40.7128, -74)))
            writeToFile(Query.contains("location", listOf(listOf(40.7128, -74), listOf(40.7128, -74))))
            writeToFile(Query.notContains("location", listOf(listOf(40.7128, -74), listOf(40.7128, -74))))
            writeToFile(Query.equal("location", listOf(listOf(40.7128, -74), listOf(40.7128, -74))))
            writeToFile(Query.notEqual("location", listOf(listOf(40.7128, -74), listOf(40.7128, -74))))
            
            writeToFile(Query.or(listOf(Query.equal("released", listOf(true)), Query.lessThan("releasedYear", 1990))))
            writeToFile(Query.and(listOf(Query.equal("released", listOf(false)), Query.greaterThan("releasedYear", 2015))))

            // regex, exists, notExists, elemMatch
            writeToFile(Query.regex("name", "pattern.*"))
            writeToFile(Query.exists(listOf("attr1", "attr2")))
            writeToFile(Query.notExists(listOf("attr1", "attr2")))
            writeToFile(Query.elemMatch("friends", listOf(
                Query.equal("name", "Alice"),
                Query.greaterThan("age", 18)
            )))

            // Permission & Roles helper tests
            writeToFile(Permission.read(Role.any()))
            writeToFile(Permission.write(Role.user(ID.custom("userid"))))
            writeToFile(Permission.create(Role.users()))
            writeToFile(Permission.update(Role.guests()))
            writeToFile(Permission.delete(Role.team("teamId", "owner")))
            writeToFile(Permission.delete(Role.team("teamId")))
            writeToFile(Permission.create(Role.member("memberId")))
            writeToFile(Permission.update(Role.users("verified")))
            writeToFile(Permission.update(Role.user(ID.custom("userid"), "unverified")))
            writeToFile(Permission.create(Role.label("admin")))

            // ID helper tests
            writeToFile(ID.unique())
            writeToFile(ID.custom("custom_id"))

            // Topic helper tests
            writeToFile(Topic.path(listOf("user", "123", "notification")).toString())
            writeToFile(Topic.path(listOf("org", "42", "user", "123")).path(listOf("notification")).toString())
            writeToFile(Topic.path(listOf("user")).any().path(listOf("notification")).toString())
            writeToFile(Topic.path(listOf("chat")).any().any().path(listOf("message")).toString())
            writeToFile(Topic.path(listOf("org")).any().path(listOf("logs")).all().toString())
            writeToFile(Topic.any().path(listOf("notification")).toString())
            writeToFile(Topic.all().toString())
            val topicErrorCases = listOf(
                "empty path" to emptyList<String>(),
                "empty level" to listOf("user", ""),
                "slash" to listOf("user/123"),
                "plus" to listOf("user", "a+b"),
                "hash" to listOf("user", "#"),
            )
            for ((name, levels) in topicErrorCases) {
                try {
                    Topic.path(levels)
                    writeToFile("Topic $name:failed")
                } catch (e: IllegalArgumentException) {
                    writeToFile("Topic $name:passed")
                }
            }

            // Channel helper tests
            writeToFile(Channel.database("db1").collection("col1").document().toString())
            writeToFile(Channel.database("db1").collection("col1").document("doc1").toString())
            writeToFile(Channel.database("db1").collection("col1").document("doc1").create().toString())
            writeToFile(Channel.database("db1").collection("col1").document("doc1").upsert().toString())
            writeToFile(Channel.tablesdb("db1").table("table1").row().toString())
            writeToFile(Channel.tablesdb("db1").table("table1").row("row1").toString())
            writeToFile(Channel.tablesdb("db1").table("table1").row("row1").update().toString())
            writeToFile(Channel.account())
            writeToFile(Channel.bucket("bucket1").file().toString())
            writeToFile(Channel.bucket("bucket1").file("file1").toString())
            writeToFile(Channel.bucket("bucket1").file("file1").delete().toString())
            writeToFile(Channel.function("func2").toString())
            writeToFile(Channel.function("func1").toString())
            writeToFile(Channel.execution("exec2").toString())
            writeToFile(Channel.execution("exec1").toString())
            writeToFile(Channel.documents())
            writeToFile(Channel.rows())
            writeToFile(Channel.files())
            writeToFile(Channel.executions())
            writeToFile(Channel.teams())
            writeToFile(Channel.team("team2").toString())
            writeToFile(Channel.team("team1").toString())
            writeToFile(Channel.team("team1").create().toString())
            writeToFile(Channel.memberships())
            writeToFile(Channel.membership("membership2").toString())
            writeToFile(Channel.membership("membership1").toString())
            writeToFile(Channel.membership("membership1").update().toString())
            writeToFile(Channel.presences())
            writeToFile(Channel.presence("presence2").toString())
            writeToFile(Channel.presence("presence1").toString())
            writeToFile(Channel.presence("presence1").upsert().toString())
            writeToFile(Channel.presence("presence1").update().toString())
            writeToFile(Channel.presence("presence1").delete().toString())

            // Operator helper tests
            writeToFile(Operator.increment(1))
            writeToFile(Operator.increment(5, 100))
            writeToFile(Operator.decrement(1))
            writeToFile(Operator.decrement(3, 0))
            writeToFile(Operator.multiply(2))
            writeToFile(Operator.multiply(3, 1000))
            writeToFile(Operator.divide(2))
            writeToFile(Operator.divide(4, 1))
            writeToFile(Operator.modulo(5))
            writeToFile(Operator.power(2))
            writeToFile(Operator.power(3, 100))
            writeToFile(Operator.arrayAppend(listOf("item1", "item2")))
            writeToFile(Operator.arrayPrepend(listOf("first", "second")))
            writeToFile(Operator.arrayInsert(0, "newItem"))
            writeToFile(Operator.arrayRemove("oldItem"))
            writeToFile(Operator.arrayUnique())
            writeToFile(Operator.arrayIntersect(listOf("a", "b", "c")))
            writeToFile(Operator.arrayDiff(listOf("x", "y")))
            writeToFile(Operator.arrayFilter(Condition.EQUAL, "test"))
            writeToFile(Operator.stringConcat("suffix"))
            writeToFile(Operator.stringReplace("old", "new"))
            writeToFile(Operator.toggle())
            writeToFile(Operator.dateAddDays(7))
            writeToFile(Operator.dateSubDays(3))
            writeToFile(Operator.dateSetNow())

            mock = general.headers()
            writeToFile(mock.result)

            // Native push (MQTT): subscribe, then the mock broker delivers a message
            // (server-initiated, as in production — the SDK has no publish method).
            client.setJWT("eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VySWQiOiJlMmUtdXNlciJ9.e2e")
            client.setPushEndpoint("mqtt://mqtt:1883")
            val push = Push(client, ApplicationProvider.getApplicationContext())
            val pushOpenLatch = java.util.concurrent.CountDownLatch(1)
            push.onOpen { pushOpenLatch.countDown() }
            val pushLatch = java.util.concurrent.CountDownLatch(1)
            var pushBody = "Push message:failed"
            var pushQos = "Push qos:failed"
            val pushSub = push.subscribe(listOf(Topic.path(listOf("e2e-push")))) { message ->
                if (message.data == "push-payload" && message.topic == "e2e-push") {
                    pushBody = "Push message:passed"
                }
                if (message.qos == 1) {
                    pushQos = "Push qos:passed"
                }
                pushLatch.countDown()
            }
            writeToFile("Push subscribe:passed")
            writeToFile(
                if (pushOpenLatch.await(10, java.util.concurrent.TimeUnit.SECONDS)) {
                    "Push open:passed"
                } else {
                    "Push open:failed"
                },
            )
            pushLatch.await(10, java.util.concurrent.TimeUnit.SECONDS)
            writeToFile(pushBody)
            // reliableDelivery (default) => QoS 1 end to end.
            writeToFile(pushQos)
            pushSub.unsubscribe()
            push.close()

            // Topic-less subscribe: the signed-in user's own topic, users/<userId>. After each
            // SUBSCRIBE the mock publishes to users/e2e-user, users/e2e-session-user and
            // users/other-user, so a client passes only if it receives its own topic and nothing
            // else (an over-broad users/+ or users/# subscription would also get the others).
            val e2eSession = "eyJpZCI6ImUyZS1zZXNzaW9uLXVzZXIiLCJzZWNyZXQiOiJlMmUtc2VjcmV0In0="
            val userTopicsOf = { userClient: Client ->
                val userPush = Push(userClient, ApplicationProvider.getApplicationContext())
                val received = java.util.concurrent.CopyOnWriteArrayList<String>()
                // The topic-less form defaults to background = true, which hands the connection to the
                // foreground Service; Robolectric records a started Service without running it, so
                // stay in-process here.
                val userSub = userPush.subscribe(background = false) { message -> received.add(message.topic) }
                Thread.sleep(3000)
                userSub.unsubscribe()
                userPush.close()
                received.toList()
            }
            val onlyTopic = { received: List<String>, expected: String ->
                received.isNotEmpty() && received.all { it == expected }
            }
            val pushClient = {
                Client(ApplicationProvider.getApplicationContext())
                    .setProject("console")
                    .addHeader("Origin", "http://localhost")
                    .setSelfSigned(true)
                    .setPushEndpoint("mqtt://mqtt:1883")
            }

            // JWT and session both set: the JWT's user wins.
            client.setSession(e2eSession)
            val jwtTopics = userTopicsOf(client)
            writeToFile(if (onlyTopic(jwtTopics, "users/e2e-user")) "Push user topic:passed" else "Push user topic:failed")

            // Session only: the user id comes from the session secret.
            val sessionTopics = userTopicsOf(pushClient().setSession(e2eSession))
            writeToFile(
                if (onlyTopic(sessionTopics, "users/e2e-session-user")) {
                    "Push user session topic:passed"
                } else {
                    "Push user session topic:failed"
                },
            )

            // No credential: a topic-less subscribe has no user to resolve and throws.
            val anonymousPush = Push(pushClient(), ApplicationProvider.getApplicationContext())
            val noCredentialRejected = try {
                anonymousPush.subscribe { }
                false
            } catch (e: AppwriteException) {
                // The credential error itself, not any failure (setup, connection, ...).
                e.message?.contains("signed-in user") == true
            }
            anonymousPush.close()
            writeToFile(if (noCredentialRejected) "Push user no credential:passed" else "Push user no credential:failed")

            // Broker errors reach onError carrying the broker's MQTT 5 Reason String: a refused
            // CONNECT (the mock refuses a "deny:<reason>" credential with <reason>) and a server-initiated DISCONNECT
            // (the mock disconnects a client subscribing to "e2e-disconnect/<reason>" with <reason>).
            val firstError = { errorPush: Push, topic: String ->
                val errorLatch = java.util.concurrent.CountDownLatch(1)
                val errorMessage = java.util.concurrent.atomic.AtomicReference("")
                errorPush.onError { error ->
                    if (errorMessage.compareAndSet("", error.message ?: "")) {
                        errorLatch.countDown()
                    }
                }
                try {
                    errorPush.subscribe(topic) { }
                } catch (e: Exception) {
                }
                errorLatch.await(5, java.util.concurrent.TimeUnit.SECONDS)
                errorPush.close()
                errorMessage.get()
            }
            val deniedError = firstError(
                Push(pushClient().setJWT("deny:refused-by-test"), ApplicationProvider.getApplicationContext()),
                "e2e-push",
            )
            writeToFile(
                if (deniedError == "refused-by-test") "Push connect error:passed" else "Push connect error:failed",
            )
            val kickedError = firstError(Push(client, ApplicationProvider.getApplicationContext()), "e2e-disconnect/kicked-by-test")
            writeToFile(
                if (kickedError == "kicked-by-test") {
                    "Push disconnect error:passed"
                } else {
                    "Push disconnect error:failed"
                },
            )

            // Background delivery, used the way an app does: subscribe with background on and a
            // PushReceiver declared, then the process dies, and the scheduled wake-up brings the
            // next message to the receiver and a notification. Sign-out stops it.
            val context = ApplicationProvider.getApplicationContext<android.app.Application>()
            val notifications = context.getSystemService(android.app.NotificationManager::class.java)
            org.robolectric.Shadows.shadowOf(context.packageManager).addResolveInfoForIntent(
                android.content.Intent("io.appwrite.push.MESSAGE").setPackage(context.packageName),
                android.content.pm.ResolveInfo().apply {
                    activityInfo = android.content.pm.ActivityInfo().apply {
                        name = E2EPushReceiver::class.java.name
                        packageName = context.packageName
                    }
                },
            )
            val backgroundPush = Push(pushClient().setSession(e2eSession), context)
            val liveLatch = java.util.concurrent.CountDownLatch(1)
            backgroundPush.subscribe("e2e-push", background = true, title = "E2E title") { message ->
                if (message.data == "push-payload") {
                    liveLatch.countDown()
                }
            }
            writeToFile(
                if (liveLatch.await(10, java.util.concurrent.TimeUnit.SECONDS)) {
                    "Push background message:passed"
                } else {
                    "Push background message:failed"
                },
            )
            val alarms = org.robolectric.Shadows.shadowOf(context.getSystemService(android.app.AlarmManager::class.java))

            // The process dies: callbacks and the connection are gone, only what was saved remains.
            io.appwrite.services.PushBackground.dropProcessState(context)
            notifications.cancelAll()
            E2EPushReceiver.messages.clear()
            // Android fires the wake-up the SDK scheduled. This test runs without a manifest, so
            // register the alarm's receiver the way the merged manifest declares it.
            val wakeUp = alarms.nextScheduledAlarm?.operation
            if (wakeUp != null) {
                val wakeUpIntent = org.robolectric.Shadows.shadowOf(wakeUp).savedIntent
                val receiver = Class.forName(wakeUpIntent.component!!.className).getDeclaredConstructor().newInstance()
                val filter = android.content.IntentFilter(wakeUpIntent.action)
                if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.TIRAMISU) {
                    context.registerReceiver(receiver as android.content.BroadcastReceiver, filter, android.content.Context.RECEIVER_NOT_EXPORTED)
                } else {
                    context.registerReceiver(receiver as android.content.BroadcastReceiver, filter)
                }
                wakeUp.send()
            }
            val deadline = System.currentTimeMillis() + 10_000
            while (E2EPushReceiver.messages.isEmpty() && System.currentTimeMillis() < deadline) {
                org.robolectric.Shadows.shadowOf(android.os.Looper.getMainLooper()).idle()
                Thread.sleep(100)
            }
            val posted = org.robolectric.Shadows.shadowOf(notifications).allNotifications.firstOrNull()
            val postedTitle = posted?.extras?.getCharSequence(android.app.Notification.EXTRA_TITLE)?.toString()
            val postedText = posted?.extras?.getCharSequence(android.app.Notification.EXTRA_TEXT)?.toString()
            writeToFile(
                if (E2EPushReceiver.messages.toList() == listOf("push-payload") && postedTitle == "E2E title" && postedText == "push-payload") {
                    "Push background restore:passed"
                } else {
                    "Push background restore:failed"
                },
            )

            // Sign-out: close() stops background delivery, including what the earlier run saved.
            Push(pushClient().setSession(e2eSession), context).close()
            writeToFile(
                if (context.getSystemService(android.app.job.JobScheduler::class.java).allPendingJobs.isEmpty() && alarms.nextScheduledAlarm == null) {
                    "Push background close:passed"
                } else {
                    "Push background close:failed"
                },
            )

            // A refused credential stops background delivery and reaches onError.
            val refusedPush = Push(pushClient().setJWT("deny:refused-in-background"), context)
            val refusedLatch = java.util.concurrent.CountDownLatch(1)
            val refusedMessage = java.util.concurrent.atomic.AtomicReference("")
            refusedPush.onError { error ->
                if (refusedMessage.compareAndSet("", error.message ?: "")) {
                    refusedLatch.countDown()
                }
            }
            refusedPush.subscribe("e2e-push", background = true) { }
            refusedLatch.await(10, java.util.concurrent.TimeUnit.SECONDS)
            Thread.sleep(500)
            writeToFile(
                if (refusedMessage.get() == "refused-in-background" && context.getSystemService(android.app.job.JobScheduler::class.java).allPendingJobs.isEmpty()) {
                    "Push background refused:passed"
                } else {
                    "Push background refused:failed"
                },
            )
            refusedPush.close()
        }
    }

    private fun writeToFile(string: String?) {
        val text = "${string ?: ""}\n"
        File("result.txt").appendText(text)
    }

    private fun parse(json: String): String? {
        return try {
            json.fromJson<Map<String, Any>>()["result"] as? String
        } catch (exception: Exception) {
            null
        }
    }
}

@Config(manifest=Config.NONE)
@RunWith(AndroidJUnit4::class)
class RequestNullEncodingTest {
    @Test
    fun testClientJsonBodyPreservesNestedNullsAndOmitsOptionalParams() = runBlocking {
        val client = Client(ApplicationProvider.getApplicationContext())
            .setProject("123456")
            .setSelfSigned(true)
        val request = client.prepareRequest(
            method = "PATCH",
            path = "/v1/tablesdb/db/tables/tbl/rows/row",
            headers = mapOf("content-type" to "application/json"),
            params = mapOf(
                "data" to mapOf(
                    "backgroundType" to "SOLID_COLOR",
                    "solidColorIndex" to null,
                ),
                "permissions" to null,
            )
        )

        val body = requestBodyUtf8(request.body!!)
        assertTrue(body.contains("\"solidColorIndex\":null"))
        assertTrue(body.contains("\"backgroundType\":\"SOLID_COLOR\""))
        assertFalse(body.contains("permissions"))
    }

    @Test
    fun testClientGetOmitsNullQueryParams() = runBlocking {
        val client = Client(ApplicationProvider.getApplicationContext())
            .setProject("123456")
            .setSelfSigned(true)
        val request = client.prepareRequest(
            method = "GET",
            path = "/v1/mock/tests/foo",
            params = mapOf(
                "x" to "1",
                "empty" to null,
            )
        )

        val url = request.url.toString()
        assertTrue(url.contains("x=1"))
        assertFalse(url.contains("empty"))
        assertFalse(url.contains("null"))
    }

    @Test
    fun testClientMultipartOmitsNullParts() = runBlocking {
        val client = Client(ApplicationProvider.getApplicationContext())
            .setProject("123456")
            .setSelfSigned(true)
        val request = client.prepareRequest(
            method = "POST",
            path = "/v1/storage/files",
            headers = mapOf("content-type" to MultipartBody.FORM.toString()),
            params = mapOf(
                "fileId" to "abc",
                "permissions" to null,
            )
        )

        val body = request.body as MultipartBody
        val dispositions = body.parts.map { part ->
            part.headers?.get("Content-Disposition").orEmpty()
        }
        assertTrue(dispositions.any { it.contains("fileId") })
        assertFalse(dispositions.any { it.contains("permissions") })
        assertFalse(requestBodyUtf8(body).contains("null"))
    }

    private fun requestBodyUtf8(body: okhttp3.RequestBody): String {
        val buffer = Buffer()
        body.writeTo(buffer)
        return buffer.readUtf8()
    }
}
