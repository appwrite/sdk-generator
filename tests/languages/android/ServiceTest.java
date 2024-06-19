package io.appwrite;

import static io.appwrite.CoroutineTestUtils.resetTestMainDispatcher;
import static io.appwrite.CoroutineTestUtils.scopeFor;
import static io.appwrite.CoroutineTestUtils.setTestMainDispatcher;
import static kotlinx.coroutines.DelayKt.delay;

import androidx.test.core.app.ApplicationProvider;
import androidx.test.ext.junit.runners.AndroidJUnit4;

import org.junit.After;
import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.annotation.Config;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.util.Arrays;
import java.util.Collections;
import java.util.Map;

import io.appwrite.coroutines.CoroutineCallback;
import io.appwrite.enums.MockType;
import io.appwrite.models.Error;
import io.appwrite.models.InputFile;
import io.appwrite.models.Mock;
import io.appwrite.services.Bar;
import io.appwrite.services.Foo;
import io.appwrite.services.General;
import io.appwrite.services.Realtime;
import kotlin.Unit;
import kotlinx.coroutines.ExperimentalCoroutinesApi;
import kotlinx.coroutines.test.TestScope;

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
    private TestScope testScope;

    private final String filename = "result.txt";

    @Before
    @ExperimentalCoroutinesApi
    public void setUp() throws IOException {
        testScope = scopeFor(setTestMainDispatcher());
        Files.deleteIfExists(Paths.get(filename));
        writeToFile("Test Started");
    }

    @After
    @ExperimentalCoroutinesApi
    public void tearDown() {
        resetTestMainDispatcher();
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

        realtime.subscribe(new String[]{"tests"}, TestPayload.class, payload -> {
            realtimeResponse[0] = payload.getPayload().getResponse();
            return Unit.INSTANCE;
        });

        CoroutineTestUtils.runTest(testScope, continuation -> {
            foo.get("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.post("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.put("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.patch("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            foo.delete("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));

            bar.get("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.post("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.put("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.patch("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));
            bar.delete("string", 123, Collections.singletonList("string in array"), new CoroutineCallback<>(this::writeMockResult));

            general.redirect(new CoroutineCallback<>((result, error) -> {
                if (result != null) {
                    writeToFile(((Map<String, Object>) result).get("result").toString());
                }
            }));

            general.upload("string", 123, Collections.singletonList("string in array"), InputFile.fromPath("../../../resources/file.png"), null, new CoroutineCallback<>(this::writeMockResult));
            general.upload("string", 123, Collections.singletonList("string in array"), InputFile.fromPath("../../../resources/large_file.mp4"), null, new CoroutineCallback<>(this::writeMockResult));

            byte[] bytes;
            try {
                bytes = Files.readAllBytes(Paths.get("../../../resources/file.png"));
            } catch (IOException e) {
                throw new RuntimeException(e);
            }
            general.upload("string", 123, Collections.singletonList("string in array"), InputFile.fromBytes(bytes, "file.png", "image/png"), null, new CoroutineCallback<>(this::writeMockResult));

            try {
                bytes = Files.readAllBytes(Paths.get("../../../resources/large_file.mp4"));
            } catch (IOException e) {
                throw new RuntimeException(e);
            }
            general.upload("string", 123, Collections.singletonList("string in array"), InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"), null, new CoroutineCallback<>(this::writeMockResult));

            general.xenum(MockType.FIRST, new CoroutineCallback<>(this::writeMockResult));

            general.error400(new CoroutineCallback<>(this::writeErrorResult));
            general.error500(new CoroutineCallback<>(this::writeErrorResult));
            general.error502(new CoroutineCallback<>(this::writeObjectResult));

            general.empty(new CoroutineCallback<>((result, error) -> {
                // No-op
            }));

            writeToFile(Query.equal("released", Collections.singletonList(true)));
            writeToFile(Query.equal("title", Arrays.asList("Spiderman", "Dr. Strange")));
            writeToFile(Query.notEqual("title", "Spiderman"));
            writeToFile(Query.lessThan("releasedYear", 1990));
            writeToFile(Query.greaterThan("releasedYear", 1990));
            writeToFile(Query.search("name", "john"));
            writeToFile(Query.isNull("name"));
            writeToFile(Query.isNotNull("name"));
            writeToFile(Query.between("age", 50, 100));
            writeToFile(Query.between("age", 50.5, 100.5));
            writeToFile(Query.between("name", "Anna", "Brad"));
            writeToFile(Query.startsWith("name", "Ann"));
            writeToFile(Query.endsWith("name", "nne"));
            writeToFile(Query.select(Arrays.asList("name", "age")));
            writeToFile(Query.orderAsc("title"));
            writeToFile(Query.orderDesc("title"));
            writeToFile(Query.cursorAfter("my_movie_id"));
            writeToFile(Query.cursorBefore("my_movie_id"));
            writeToFile(Query.limit(50));
            writeToFile(Query.offset(20));
            writeToFile(Query.contains("title", Collections.singletonList("Spider")));
            writeToFile(Query.contains("labels", Collections.singletonList("first")));
            writeToFile(Query.or(Arrays.asList(Query.equal("released", Collections.singletonList(true)), Query.lessThan("releasedYear", 1990))));
            writeToFile(Query.and(Arrays.asList(Query.equal("released", Collections.singletonList(false)), Query.greaterThan("releasedYear", 2015))));

            writeToFile(Permission.read(Role.any()));
            writeToFile(Permission.write(Role.user(ID.custom("userid"))));
            writeToFile(Permission.create(Role.users()));
            writeToFile(Permission.update(Role.guests()));
            writeToFile(Permission.delete(Role.team("teamId", "owner")));
            writeToFile(Permission.delete(Role.team("teamId")));
            writeToFile(Permission.create(Role.member("memberId")));
            writeToFile(Permission.update(Role.users("verified")));
            writeToFile(Permission.update(Role.user(ID.custom("userid"), "unverified")));
            writeToFile(Permission.create(Role.label("admin")));

            writeToFile(ID.unique());
            writeToFile(ID.custom("custom_id"));

            general.headers(new CoroutineCallback<>(this::writeMockResult));

            delay(5000, new CoroutineCallback<>((result, error) -> {
                writeToFile(realtimeResponse[0]);
                continuation.resumeWith(Unit.INSTANCE);
            }));

            return null;
        });
    }

    private void writeMockResult(Mock result, Throwable error) {
        if (result != null) {
            writeToFile(result.getResult());
        } else {
            writeToFile(error.toString());
        }
    }

    private void writeErrorResult(Error result, Throwable error) {
        if (result != null) {
            writeToFile(result.getMessage());
        } else {
            writeToFile(error.getMessage());
        }
    }

    private void writeObjectResult(Object result, Throwable error) {
        if (result != null) {
            writeToFile(result.toString());
        } else {
            writeToFile(error.getMessage());
        }
    }

    private void writeToFile(String string) {
        String text = (string != null ? string : "") + "\n";
        try {
            Files.write(
                    Paths.get(filename),
                    text.getBytes(StandardCharsets.UTF_8),
                    StandardOpenOption.APPEND
            );
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
