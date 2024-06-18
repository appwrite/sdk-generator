package io.appwrite;

import androidx.test.core.app.ApplicationProvider;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import com.google.gson.Gson;

import io.appwrite.exceptions.AppwriteException;
import io.appwrite.Permission;
import io.appwrite.Role;
import io.appwrite.ID;
import io.appwrite.Query;
import io.appwrite.enums.MockType;
import io.appwrite.extensions.GsonExtensionsKt;
import io.appwrite.models.Error;
import io.appwrite.models.InputFile;
import io.appwrite.models.Mock;
import io.appwrite.services.Bar;
import io.appwrite.services.Foo;
import io.appwrite.services.General;
import io.appwrite.services.Realtime;

import kotlinx.coroutines.Dispatchers;
import kotlinx.coroutines.ExperimentalCoroutinesApi;
import kotlinx.coroutines.delay;
import kotlinx.coroutines.runBlocking;
import kotlinx.coroutines.test.TestCoroutineDispatcher;
import kotlinx.coroutines.test.TestCoroutineScope;

import okhttp3.Response;

import org.junit.After;
import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.annotation.Config;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.util.List;
import java.util.Map;

import static kotlinx.coroutines.test.TestCoroutineScopeKt.*;

class TestPayload {
    private String response;

    public String getResponse() {
        return response;
    }

    public void setResponse(String response) {
        this.response = response;
    }
}

@Config(manifest = Config.NONE)
@RunWith(AndroidJUnit4.class)
public class ServiceTest {

    private final String filename = "result.txt";

    @Before
    @ExperimentalCoroutinesApi
    public void setUp() throws IOException {
        Dispatchers.setMain(new TestCoroutineDispatcher());
        Files.deleteIfExists(Paths.get(filename));
        writeToFile("Test Started");
    }

    @After
    @ExperimentalCoroutinesApi
    public void tearDown() {
        Dispatchers.resetMain();
    }

    @Test
    @Throws(IOException.class)
    public void test() throws IOException {
        Client client = new Client(ApplicationProvider.getApplicationContext())
                .setEndpointRealtime("wss://demo.appwrite.io/v1")
                .setProject("console")
                .addHeader("Origin", "http://localhost")
                .setSelfSigned(true);

        Foo foo = new Foo(client);
        Bar bar = new Bar(client);
        General general = new General(client);
        Realtime realtime = new Realtime(client);
        String realtimeResponse = "Realtime failed!";

        realtime.subscribe("tests", TestPayload.class, payload -> {
            realtimeResponse = payload.getResponse();
            return null;
        });

        runBlocking(new TestCoroutineScope(), () -> {
            Mock mock;
            // Foo Tests
            mock = foo.get("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = foo.post("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = foo.put("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = foo.patch("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = foo.delete("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());

            // Bar Tests
            mock = bar.get("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = bar.post("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = bar.put("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = bar.patch("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());
            mock = bar.delete("string", 123, List.of("string in array"));
            writeToFile(mock.getResult());

            // General Tests
            Object result = general.redirect();
            writeToFile((String) ((Map<String, Object>) result).get("result"));

            try {
                mock = general.upload("string", 123, List.of("string in array"), InputFile.fromPath("../../../resources/file.png"));
                writeToFile(mock.getResult());
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            try {
                mock = general.upload("string", 123, List.of("string in array"), InputFile.fromPath("../../../resources/large_file.mp4"));
                writeToFile(mock.getResult());
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            try {
                byte[] bytes = Files.readAllBytes(Paths.get("../../../resources/file.png"));
                mock = general.upload("string", 123, List.of("string in array"), InputFile.fromBytes(bytes, "file.png", "image/png"));
                writeToFile(mock.getResult());
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            try {
                byte[] bytes = Files.readAllBytes(Paths.get("../../../resources/large_file.mp4"));
                mock = general.upload("string", 123, List.of("string in array"), InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"));
                writeToFile(mock.getResult());
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            mock = general.enum(MockType.FIRST);
            writeToFile(mock.getResult());

            try {
                general.error400();
            } catch (AppwriteException e) {
                writeToFile(e.getMessage());
            }

            try {
                general.error500();
            } catch (AppwriteException e) {
                writeToFile(e.getMessage());
            }

            try {
                general.error502();
            } catch (AppwriteException e) {
                writeToFile(e.getMessage());
            }

            delay(5000);
            writeToFile(realtimeResponse);

            // mock = general.setCookie();
            // writeToFile(mock.getResult());

            // mock = general.getCookie();
            // writeToFile(mock.getResult());

            general.empty();

            // Query helper tests
            writeToFile(Query.Companion.equal("released", List.of(true)).toString());
            writeToFile(Query.Companion.equal("title", List.of("Spiderman", "Dr. Strange")).toString());
            writeToFile(Query.Companion.notEqual("title", "Spiderman").toString());
            writeToFile(Query.Companion.lessThan("releasedYear", 1990).toString());
            writeToFile(Query.Companion.greaterThan("releasedYear", 1990).toString());
            writeToFile(Query.Companion.search("name", "john").toString());
            writeToFile(Query.Companion.isNull("name").toString());
            writeToFile(Query.Companion.isNotNull("name").toString());
            writeToFile(Query.Companion.between("age", 50, 100).toString());
            writeToFile(Query.Companion.between("age", 50.5, 100.5).toString());
            writeToFile(Query.Companion.between("name", "Anna", "Brad").toString());
            writeToFile(Query.Companion.startsWith("name", "Ann").toString());
            writeToFile(Query.Companion.endsWith("name", "nne").toString());
            writeToFile(Query.Companion.select(List.of("name", "age")).toString());
            writeToFile(Query.Companion.orderAsc("title").toString());
            writeToFile(Query.Companion.orderDesc("title").toString());
            writeToFile(Query.Companion.cursorAfter("my_movie_id").toString());
            writeToFile(Query.Companion.cursorBefore("my_movie_id").toString());
            writeToFile(Query.Companion.limit(50).toString());
            writeToFile(Query.Companion.offset(20).toString());
            writeToFile(Query.Companion.contains("title", List.of("Spider")).toString());
            writeToFile(Query.Companion.contains("labels", List.of("first")).toString());
            writeToFile(Query.Companion.or(List.of(Query.Companion.equal("released", List.of(true)), Query.Companion.lessThan("releasedYear", 1990))).toString());
            writeToFile(Query.Companion.and(List.of(Query.Companion.equal("released", List.of(false)), Query.Companion.greaterThan("releasedYear", 2015))).toString());

            // Permission & Roles helper tests
            writeToFile(Permission.Companion.read(Role.Companion.any()).toString());
            writeToFile(Permission.Companion.write(Role.Companion.user(ID.custom("userid"))).toString());
            writeToFile(Permission.Companion.create(Role.Companion.users()).toString());
            writeToFile(Permission.Companion.update(Role.Companion.guests()).toString());
            writeToFile(Permission.Companion.delete(Role.Companion.team("teamId", "owner")).toString());
            writeToFile(Permission.Companion.delete(Role.Companion.team("teamId")).toString());
            writeToFile(Permission.Companion.create(Role.Companion.member("memberId")).toString());
            writeToFile(Permission.Companion.update(Role.Companion.users("verified")).toString());
            writeToFile(Permission.Companion.update(Role.Companion.user(ID.custom("userid"), "unverified")).toString());
            writeToFile(Permission.Companion.create(Role.Companion.label("admin")).toString());

            // ID helper tests
            writeToFile(ID.unique().toString());
            writeToFile(ID.custom("custom_id").toString());

            mock = general.headers();
            writeToFile(mock.getResult());

            return null;
        });
    }

    private void writeToFile(String string) {
        String text = (string != null ? string : "") + "\n";
        try {
            Files.writeString(
                Paths.get(filename),
                text,
                StandardOpenOption.APPEND
            );
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
