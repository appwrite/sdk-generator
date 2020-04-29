package io.appwrite.services;

import com.google.gson.Gson;
import org.junit.jupiter.api.Test;
import io.appwrite.Client;
import okhttp3.Response;
import java.util.List;
import java.io.IOException;
import java.util.Map;

public class ServiceTest {

    @Test
    public void test() throws IOException {
        Client client = new Client();
        Foo foo = new Foo(client);
        Bar bar = new Bar(client);
        General general = new General(client);

        client.addHeader("Origin", "http://localhost");
        client.setSelfSigned(true);

// Foo Tests

        Response response;
        response = foo.get("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = foo.post("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = foo.put("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = foo.patch("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = foo.delete("string", 123, List.of("string in array")).execute();
        printResponse(response);

// Bar Tests

        response = bar.get("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = bar.post("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = bar.put("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = bar.patch("string", 123, List.of("string in array")).execute();
        printResponse(response);

        response = bar.delete("string", 123, List.of("string in array")).execute();
        printResponse(response);

        // General Tests

        response = general.redirect().execute();
        printResponse(response);

        // response = await general.setCookie();
        // System.out.println(response.data["result"]);

        // response = await general.getCookie();
        // System.out.println(response.data["result"]);
    }

    private void printResponse(Response response) throws IOException {
        Gson gson = new Gson();
        Map map = gson.fromJson(response.body().string(), Map.class);
        System.out.println(map.get("result"));

    }
}