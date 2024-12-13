package io.appwrite

import com.google.gson.Gson
import io.appwrite.Permission
import io.appwrite.Role
import io.appwrite.ID
import io.appwrite.Query
import io.appwrite.enums.MockType
import io.appwrite.exceptions.AppwriteException
import io.appwrite.extensions.fromJson
import io.appwrite.extensions.toJson
import io.appwrite.models.Error
import io.appwrite.models.InputFile
import io.appwrite.models.Mock
import io.appwrite.services.Bar
import io.appwrite.services.Foo
import io.appwrite.services.General
import kotlinx.coroutines.runBlocking
import okhttp3.Response
import org.junit.Before
import org.junit.Test
import java.io.File
import java.io.IOException
import java.nio.file.Files
import java.nio.file.Paths

class ServiceTest {

    private val filename: String = "result.txt"

    @Before
    fun setUp() {
        Files.deleteIfExists(Paths.get(filename))
        writeToFile("Test Started")
    }

    @Test
    @Throws(IOException::class)
    fun test() {
        val client = Client()
            .setProject("123456")
            .addHeader("Origin", "http://localhost")
            .setSelfSigned(true)

        runBlocking {
            val ping = client.ping()
            val pingResponse = parse(ping)
            writeToFile(pingResponse)
        }

        // reset project
        client.setProject("123456")

        val foo = Foo(client)
        val bar = Bar(client)
        val general = General(client)

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
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromPath("../../resources/file.png"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }
            try {
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromPath("../../resources/large_file.mp4"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }
            try {
                var bytes = File("../../resources/file.png").readBytes()
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromBytes(bytes, "file.png", "image/png"))
                writeToFile(mock.result)
            } catch (ex: Exception) {
                writeToFile(ex.toString())
            }
            try {
                var bytes = File("../../resources/large_file.mp4").readBytes()
                mock = general.upload("string", 123, listOf("string in array"), InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"))
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
            }

            try {
                general.error500()
            } catch (e: AppwriteException) {
                writeToFile(e.message)
            }

            try {
                general.error502()
            } catch (e: AppwriteException) {
                writeToFile(e.message)
            }

            general.empty()

            val url = general.oauth2(
                clientId = "clientId",
                scopes = listOf("test"),
                state = "123456",
                success = "https://localhost",
                failure = "https://localhost",
            )
            writeToFile(url)

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
            writeToFile(Query.or(listOf(Query.equal("released", listOf(true)), Query.lessThan("releasedYear", 1990))))
            writeToFile(Query.and(listOf(Query.equal("released", listOf(false)), Query.greaterThan("releasedYear", 2015))))

            // Permission & Roles helper tests
            writeToFile(Permission.read(Role.any()))
            writeToFile(Permission.write(Role.user(ID.custom("userid"))))
            writeToFile(Permission.create(Role.users()))
            writeToFile(Permission.update(Role.guests()))
            writeToFile(Permission.delete(Role.team("teamId", "owner")))
            writeToFile(Permission.delete(Role.team("teamId")))
            writeToFile(Permission.create(Role.member("memberId")))
            writeToFile(Permission.update(Role.users("verified")));
            writeToFile(Permission.update(Role.user(ID.custom("userid"), "unverified")));
            writeToFile(Permission.create(Role.label("admin")));

            // ID helper tests
            writeToFile(ID.unique())
            writeToFile(ID.custom("custom_id"))

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