package io.appwrite

import androidx.test.core.app.ApplicationProvider
import androidx.test.ext.junit.runners.AndroidJUnit4
import com.google.gson.Gson
import io.appwrite.exceptions.AppwriteException
import io.appwrite.extensions.fromJson
import io.appwrite.extensions.toJson
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
            .setEndpointRealtime("wss://demo.appwrite.io/v1")
            .setProject("console")
            .addHeader("Origin", "http://localhost")
            .setSelfSigned(true)
        val foo = Foo(client)
        val bar = Bar(client)
        val general = General(client)
        val realtime = Realtime(client)
        var realtimeResponse = "Realtime failed!"

        realtime.subscribe("tests", payloadType = TestPayload::class.java) {
            realtimeResponse = it.payload.response
        }

        runBlocking {
            var response: Response
            // Foo Tests
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

            response = general.upload("string", 123, listOf("string in array"), File("../../../resources/file.png"))
            printResponse(response)

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

            delay(5000)
            writeToFile(realtimeResponse)
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

    private fun writeToFile(string: String?) {
        val text = "${string ?: ""}\n"
        File("result.txt").appendText(text)
    }

}