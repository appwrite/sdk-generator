package io.appwrite

import com.google.gson.Gson
import io.appwrite.Permission
import io.appwrite.Role
import io.appwrite.ID
import io.appwrite.Query
import io.appwrite.Operator
import io.appwrite.Condition
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

            // Operator helper tests
            writeToFile(Operator.increment(1))
            writeToFile(Operator.increment(5, 100))
            writeToFile(Operator.decrement(1))
            writeToFile(Operator.decrement(5, 0))
            writeToFile(Operator.multiply(2))
            writeToFile(Operator.divide(2))
            writeToFile(Operator.modulo(3))
            writeToFile(Operator.power(2))
            writeToFile(Operator.append("value"))
            writeToFile(Operator.append(listOf("value1", "value2")))
            writeToFile(Operator.prepend("value"))
            writeToFile(Operator.prepend(listOf("value1", "value2")))
            writeToFile(Operator.insert(0, "value"))
            writeToFile(Operator.insert(1, listOf("value1", "value2")))
            writeToFile(Operator.remove("value"))
            writeToFile(Operator.remove(listOf("value1", "value2")))
            writeToFile(Operator.unique())
            writeToFile(Operator.intersect(listOf("value1", "value2")))
            writeToFile(Operator.diff(listOf("value1", "value2")))
            writeToFile(Operator.filter(Condition.EQUAL, "value2"))
            writeToFile(Operator.concat("newValue"))
            writeToFile(Operator.replace("oldValue", "newValue"))
            writeToFile(Operator.toggle())
            writeToFile(Operator.dateAddDays(7))
            writeToFile(Operator.dateSubDays(7))
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