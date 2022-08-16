package io.appwrite

import com.google.gson.Gson
import io.appwrite.Permission
import io.appwrite.Role
import io.appwrite.ID
import io.appwrite.Query
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
            .addHeader("Origin", "http://localhost")
            .setSelfSigned(true)
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

            // Query helper tests
            writeToFile(Query.equal("title", listOf("Spiderman", "Dr. Strange")));
            writeToFile(Query.notEqual("title", "Spiderman"));
            writeToFile(Query.lesser("releasedYear", 1990));
            writeToFile(Query.greater("releasedYear", listOf(1990, 1999)));
            writeToFile(Query.search("name", "john"));
            writeToFile(Query.orderAsc("title"));
            writeToFile(Query.orderDesc("title"));
            writeToFile(Query.cursorAfter("my_movie_id"));
            writeToFile(Query.cursorBefore("my_movie_id"));
            writeToFile(Query.limit(50));
            writeToFile(Query.offset(20));

            // Permission & Roles helper tests
            writeToFile(Permission.read(Role.any()));
            writeToFile(Permission.write(Role.user(ID.custom("userid"))));
            writeToFile(Permission.create(Role.users()));
            writeToFile(Permission.update(Role.guests()));
            writeToFile(Permission.delete(Role.team("teamId", "owner")));
            writeToFile(Permission.delete(Role.team("teamId")));

            // ID helper tests
            writeToFile(ID.unique());
            writeToFile(ID.custom("custom_id"));
        }
    }

    private fun writeToFile(string: String?) {
        val text = "${string ?: ""}\n"
        File("result.txt").appendText(text)
    }

}