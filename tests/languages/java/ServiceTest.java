package io.appwrite.services;

import org.junit.jupiter.api.Test;
import io.appwrite.Client;
import okhttp3.Response;
import java.util.List;
import java.io.IOException;

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
        System.out.println(response.body().string());

        response = foo.post("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = foo.put("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = foo.patch("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = foo.delete("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

// Bar Tests

        response = bar.get("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = bar.post("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = bar.put("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = bar.patch("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        response = bar.delete("string", 123, List.of("string in array")).execute();
        System.out.println(response.body().string());

        // General Tests

        response = general.redirect().execute();
        System.out.println(response.body().string());

        // response = await general.setCookie();
        // System.out.println(response.data["result"]);

        // response = await general.getCookie();
        // System.out.println(response.data["result"]);
    }
}