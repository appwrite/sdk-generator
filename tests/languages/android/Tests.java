package io.appwrite;

import androidx.test.core.app.ApplicationProvider;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import io.appwrite.coroutines.CoroutineCallback;
import io.appwrite.exceptions.AppwriteException;
import io.appwrite.Permission;
import io.appwrite.Role;
import io.appwrite.ID;
import io.appwrite.Query;
import io.appwrite.enums.MockType;
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

import kotlin.coroutines.Continuation;
import kotlin.coroutines.CoroutineContext;
import kotlin.coroutines.Result;
import kotlin.jvm.JvmOverloads;

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
        String[] realtimeResponse = {"Realtime failed!"};

        realtime.subscribe("tests", TestPayload.class, payload -> {
            realtimeResponse[0] = payload.getResponse();
            return null;
        });

        runBlocking(Dispatchers.getDefault(), (Continuation<Unit>) continuation -> {
            foo.get("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.post("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.put("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.patch("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.delete("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));

            bar.get("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.post("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.put("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.patch("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.delete("string", 123, List.of("string in array"), new CoroutineCallback<>(this::writeMockResult));

            general.redirect(new CoroutineCallback<>((result, error) -> {
                if (result != null) {
                    writeToFile(((Map<String, Object>) result).get("result").toString());
                }
            }));

            try {
                general.upload("string", 123, List.of("string in array"), InputFile.fromPath("../../../resources/file.png"), new CoroutineCallback<>(this::writeMockResult));
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            try {
                general.upload("string", 123, List.of("string in array"), InputFile.fromPath("../../../resources/large_file.mp4"), new CoroutineCallback<>(this::writeMockResult));
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            try {
                byte[] bytes = Files.readAllBytes(Paths.get("../../../resources/file.png"));
                general.upload("string", 123, List.of("string in array"), InputFile.fromBytes(bytes, "file.png", "image/png"), new CoroutineCallback<>(this::writeMockResult));
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            try {
                byte[] bytes = Files.readAllBytes(Paths.get("../../../resources/large_file.mp4"));
                general.upload("string", 123, List.of("string in array"), InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"), new CoroutineCallback<>(this::writeMockResult));
            } catch (Exception ex) {
                writeToFile(ex.toString());
            }

            general.enum(MockType.FIRST, new CoroutineCallback<>(this::writeMockResult));

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

            general.empty();

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

            writeToFile(Permission.Companion.read(Role.Companion.any()).toString());
            writeToFile(Permission.Companion.write(Role.Companion.user(ID.Companion.custom("userid"))).toString());
            writeToFile(Permission.Companion.create(Role.Companion.users()).toString());
            writeToFile(Permission.Companion.update(Role.Companion.guests()).toString());
            writeToFile(Permission.Companion.delete(Role.Companion.team("teamId", "owner")).toString());
            writeToFile(Permission.Companion.delete(Role.Companion.team("teamId")).toString());
            writeToFile(Permission.Companion.create(Role.Companion.member("memberId")).toString());
            writeToFile(Permission.Companion.update(Role.Companion.users("verified")).toString());
            writeToFile(Permission.Companion.update(Role.Companion.user(ID.Companion.custom("userid"), "unverified")).toString());
            writeToFile(Permission.Companion.create(Role.Companion.label("admin")).toString());

            writeToFile(ID.Companion.unique().toString());
            writeToFile(ID.Companion.custom("custom_id").toString());

            general.headers(new CoroutineCallback<>(this::writeMockResult));

            delay(5000, continuation);
            writeToFile(realtimeResponse[0]);

            return Unit.INSTANCE;
        });
    }

    private void writeMockResult(Mock result, Throwable error) {
        if (result != null) {
            writeToFile(result.getResult());
        } else {
            writeToFile(error.toString());
        }
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
