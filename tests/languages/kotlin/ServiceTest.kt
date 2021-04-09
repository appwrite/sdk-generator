package io.appwrite.services

import com.google.gson.Gson
import io.appwrite.AppwriteClient
import io.appwrite.Foo
import io.appwrite.Bar
import io.appwrite.General
import okhttp3.Response
import org.junit.jupiter.api.Test
import java.io.IOException

class ServiceTest {
    @Test
    @Throws(IOException::class)
    fun test() {

        val client = AppwriteClient()
        val foo = Foo(client)
        val bar = Bar(client)
        val general = General(client)
        client.addHeader("Origin", "http://localhost")
        client.setSelfSigned(true)

        // Foo Tests
        var response: Response
        response = foo.get("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = foo.post("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = foo.put("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = foo.patch("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = foo.delete("string", 123, listOf("string in array")).execute()
        printResponse(response)

        // Bar Tests
        response = bar.get("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = bar.post("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = bar.put("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = bar.patch("string", 123, listOf("string in array")).execute()
        printResponse(response)
        response = bar.delete("string", 123, listOf("string in array")).execute()
        printResponse(response)

        // General Tests
        response = general.redirect().execute()
        printResponse(response)

        // response = await general.setCookie();
        // System.out.println(response.data["result"]);

        // response = await general.getCookie();
        // System.out.println(response.data["result"]);
    }

    @Throws(IOException::class)
    private fun printResponse(response: Response) {
        val gson = Gson()
        val map = gson.fromJson<Map<*, *>>(
            response.body!!.string(),
            MutableMap::class.java
        )
        println(map["result"])
    }
}