package io.appwrite

import androidx.test.core.app.ApplicationProvider
import io.appwrite.exceptions.AppwriteException
import io.appwrite.enums.MockType
import io.appwrite.extensions.fromJson
import io.appwrite.models.InputFile
import io.appwrite.models.Mock
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
import kotlinx.serialization.Serializable
import org.junit.After
import org.junit.Before
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.RobolectricTestRunner
import java.io.File
import java.io.IOException
import java.nio.file.Files
import java.nio.file.Paths


@Serializable
data class TestPayload(val response: String)


@RunWith(RobolectricTestRunner::class)
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
        try {
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

            realtime.subscribe(listOf("tests"), payloadType = TestPayload::class) {
                realtimeResponse = it.payload.response
            }

            runBlocking {
                var mock: Mock
                // Foo Tests
                try {
                    mock = foo.get("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed foo get: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = foo.post("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed foo post: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = foo.put("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed foo put: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = foo.patch("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed foo patch: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = foo.delete("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed foo delete: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)

                // Bar Tests
                try {
                    mock = bar.get("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed bar get: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = bar.post("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed bar post: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = bar.put("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed bar put: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = bar.patch("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed bar patch: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)
                try {
                    mock = bar.delete("string", 123, listOf("string in array"))
                } catch (e: Exception) {
                    writeToFile("Failed bar delete: ${e.message}")
                    return@runBlocking
                }
                writeToFile(mock.result)

                // General Tests
                val result = general.redirect()
                writeToFile((result as Map<String, Any>)["result"] as String)

                try {
                    mock = general.upload(
                        "string",
                        123,
                        listOf("string in array"),
                        InputFile.fromPath("../../../resources/file.png")
                    )
                    writeToFile(mock.result)
                } catch (ex: Exception) {
                    writeToFile(ex.toString())
                }

                try {
                    mock = general.upload(
                        "string",
                        123,
                        listOf("string in array"),
                        InputFile.fromPath("../../../resources/large_file.mp4")
                    )
                    writeToFile(mock.result)
                } catch (ex: Exception) {
                    writeToFile(ex.toString())
                }

                try {
                    var bytes = File("../../../resources/file.png").readBytes()
                    mock = general.upload(
                        "string",
                        123,
                        listOf("string in array"),
                        InputFile.fromBytes(bytes, "file.png", "image/png")
                    )
                    writeToFile(mock.result)
                } catch (ex: Exception) {
                    writeToFile(ex.toString())
                }

                try {
                    var bytes = File("../../../resources/large_file.mp4").readBytes()
                    mock = general.upload(
                        "string",
                        123,
                        listOf("string in array"),
                        InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4")
                    )
                    writeToFile(mock.result)
                } catch (ex: Exception) {
                    writeToFile(ex.toString())
                }

                mock = general.enum(MockType.FIRST)
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
                    writeToFile(e.message)
                }

                try {
                    client.setEndpoint("htp://cloud.appwrite.io/v1")
                } catch (e: IllegalArgumentException) {
                    writeToFile(e.message)
                }

                delay(5000)
                writeToFile(realtimeResponse)

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
                writeToFile(Query.cursorAfter("my_movie_id"))
                writeToFile(Query.cursorBefore("my_movie_id"))
                writeToFile(Query.limit(50))
                writeToFile(Query.offset(20))
                writeToFile(Query.contains("title", listOf("Spider")))
                writeToFile(Query.contains("labels", listOf("first")))
                writeToFile(
                    Query.or(
                        listOf(
                            Query.equal("released", listOf(true)),
                            Query.lessThan("releasedYear", 1990)
                        )
                    )
                )
                writeToFile(
                    Query.and(
                        listOf(
                            Query.equal("released", listOf(false)),
                            Query.greaterThan("releasedYear", 2015)
                        )
                    )
                )

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

                mock = general.headers()
                writeToFile(mock.result)
            }
        } catch (e: Exception){
            writeToFile(e.message)
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
