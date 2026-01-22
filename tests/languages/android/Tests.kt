package io.appwrite

import androidx.test.core.app.ApplicationProvider
import androidx.test.ext.junit.runners.AndroidJUnit4
import com.google.gson.Gson
import io.appwrite.exceptions.AppwriteException
import io.appwrite.Permission
import io.appwrite.Role
import io.appwrite.ID
import io.appwrite.Channel
import io.appwrite.Query
import io.appwrite.Operator
import io.appwrite.Condition
import io.appwrite.enums.MockType
import io.appwrite.extensions.fromJson
import io.appwrite.extensions.toJson
import io.appwrite.models.Error
import io.appwrite.models.InputFile
import io.appwrite.models.Mock
import io.appwrite.models.Player
import io.appwrite.services.Bar
import io.appwrite.services.Foo
import io.appwrite.services.General
import io.appwrite.services.Realtime
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.delay
import kotlinx.coroutines.runBlocking
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.setMain
import okhttp3.Response
import org.junit.After
import org.junit.Before
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.annotation.Config
import java.io.File
import java.io.IOException
import java.nio.file.Files
import java.nio.file.Paths

data class TestPayload(val response: String)

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

        runBlocking {
            val ping = client.ping()
            val pingResponse = parse(ping)
            writeToFile(pingResponse)
        }

        // reset configs
        client.setProject("console")
            .setEndpointRealtime("wss://cloud.appwrite.io/v1")

        val foo = Foo(client)
        val bar = Bar(client)
        val general = General(client)
        val realtime = Realtime(client)
        var realtimeResponse = "Realtime failed!"
        var realtimeResponseWithQueries = "Realtime failed!"

        // Subscribe without queries
        realtime.subscribe("tests", payloadType = TestPayload::class.java) {
            realtimeResponse = it.payload.response
        }

        // Subscribe with queries to ensure query list support works
        realtime.subscribe(
            "tests",
            payloadType = TestPayload::class.java,
            queries = listOf(
                Query.equal("type", listOf("tests"))
            )
        ) {
            realtimeResponseWithQueries = it.payload.response
        }

        runBlocking {
            var mock: Mock
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

            try {
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromPath("../../../resources/file.png"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            try {
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromPath("../../../resources/large_file.mp4"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            try {
                var bytes = File("../../../resources/file.png").readBytes()
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromBytes(bytes, "file.png", "image/png"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

            try {
                var bytes = File("../../../resources/large_file.mp4").readBytes()
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }

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

            delay(5000)
            writeToFile(realtimeResponse)
            writeToFile(realtimeResponseWithQueries)

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

            // Channel helper tests
            writeToFile(Channel.database().collection().document().toString())
            writeToFile(Channel.database("db1").collection("col1").document("doc1").toString())
            writeToFile(Channel.database("db1").collection("col1").document("doc1").create().toString())
            writeToFile(Channel.tablesdb().table().row().toString())
            writeToFile(Channel.tablesdb("db1").table("table1").row("row1").toString())
            writeToFile(Channel.tablesdb("db1").table("table1").row("row1").update().toString())
            writeToFile(Channel.account())
            writeToFile(Channel.account("user123"))
            writeToFile(Channel.bucket().file().toString())
            writeToFile(Channel.bucket("bucket1").file("file1").toString())
            writeToFile(Channel.bucket("bucket1").file("file1").delete().toString())
            writeToFile(Channel.function().execution().toString())
            writeToFile(Channel.function("func1").execution("exec1").toString())
            writeToFile(Channel.function("func1").execution("exec1").create().toString())
            writeToFile(Channel.team().toString())
            writeToFile(Channel.team("team1").toString())
            writeToFile(Channel.team("team1").create().toString())
            writeToFile(Channel.membership().toString())
            writeToFile(Channel.membership("membership1").toString())
            writeToFile(Channel.membership("membership1").update().toString())

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