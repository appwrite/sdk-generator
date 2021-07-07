import com.google.gson.Gson
import io.appwrite.Client
import io.appwrite.exceptions.AppwriteException
import io.appwrite.services.Bar
import io.appwrite.services.Foo
import io.appwrite.services.General
import okhttp3.Response
import kotlinx.coroutines.runBlocking
import org.junit.Before
import org.junit.Test
import java.io.File
import java.io.IOException
import java.nio.file.Files
import java.nio.file.Paths

class ServiceTest {
    val filename: String = "result.txt"

    @Before
    fun start() {
        Files.deleteIfExists(Paths.get(filename))
        writeToFile("Test Started")
    }

    @Test
    @Throws(IOException::class)
    fun test() {
        val client = Client()
        val foo = Foo(client)
        val bar = Bar(client)
        val general = General(client)
        client.addHeader("Origin", "http://localhost")
        client.setSelfSigned(true)

        var response: Response
        // Foo Tests
        runBlocking {
            response = foo.get("string", 123, listOf("string in array"))
            printResponse(response)
            response = foo.post("string", 123, listOf("string in array"))
            printResponse(response)
            response = foo.put("string", 123, listOf("string in array"))
            printResponse(response)
            response = foo.patch("string", 123, listOf("string in array"))
            printResponse(response)
            response = foo.delete("string", 123, listOf("string in array"))
            printResponse(response)

            // Bar Tests
            response = bar.get("string", 123, listOf("string in array"))
            printResponse(response)
            response = bar.post("string", 123, listOf("string in array"))
            printResponse(response)
            response = bar.put("string", 123, listOf("string in array"))
            printResponse(response)
            response = bar.patch("string", 123, listOf("string in array"))
            printResponse(response)
            response = bar.delete("string", 123, listOf("string in array"))
            printResponse(response)

            // General Tests
            response = general.redirect()
            printResponse(response)

            response = general.upload("string", 123, listOf("string in array"), File("../../resources/file.png"))
            printResponse(response)

            try {
                general.error400()
            } catch(e: AppwriteException) {
                writeToFile(e.message)
            }

            try {
                general.error500()
            } catch(e: AppwriteException) {
                writeToFile(e.message)
            }

            try {
                general.error502()
            } catch(e: AppwriteException) {
                writeToFile(e.message)
            }
        }
    }

    @Throws(IOException::class)
    private fun printResponse(response: Response) {
        // Store the outputs in a file and
        val gson = Gson()
        val map = gson.fromJson<Map<*, *>>(
            response.body!!.string(),
            MutableMap::class.java
        )
        writeToFile(map["result"] as String)
    }

    private fun writeToFile(string: String?){
        val text = "${string ?: ""}\n"
        File("result.txt").appendText(text)
    }

}