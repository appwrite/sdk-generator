package io.appwrite;

import io.appwrite.exceptions.AppwriteException;
import io.appwrite.models.InputFile;
import io.appwrite.models.Mock;
import io.appwrite.models.Player;
import io.appwrite.enums.MockType;
import io.appwrite.services.Bar;
import io.appwrite.services.Foo;
import io.appwrite.services.General;

import java.io.File;
import java.io.IOException;
import java.io.PrintWriter;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ExecutionException;

public class Tests {

    private static PrintWriter out;

    public static void main(String[] args) throws Exception {
        Files.deleteIfExists(Paths.get("result.txt"));
        out = new PrintWriter(new java.io.FileWriter("result.txt", true));

        write("Test Started");

        Client client = new Client()
            .setProject("123456")
            .addHeader("Origin", "http://localhost")
            .setSelfSigned(true);

        Map<String, String> sdkHeaders = client.getHeaders();
        write("x-sdk-name: " + sdkHeaders.get("x-sdk-name")
            + "; x-sdk-platform: " + sdkHeaders.get("x-sdk-platform")
            + "; x-sdk-language: " + sdkHeaders.get("x-sdk-language")
            + "; x-sdk-version: " + sdkHeaders.get("x-sdk-version"));

        // Ping
        try {
            String ping = (String) client.call("GET", "/ping",
                Map.of("content-type", "application/json"), Map.of(), String.class).get();
            com.google.gson.Gson gson = new com.google.gson.Gson();
            @SuppressWarnings("unchecked")
            Map<String, Object> pingMap = gson.fromJson(ping, Map.class);
            write((String) pingMap.get("result"));
        } catch (Exception e) {
            write(e.getMessage());
        }

        client.setProject("123456");

        Foo foo = new Foo(client);
        Bar bar = new Bar(client);
        General general = new General(client);

        // Foo Tests
        Mock mock;

        mock = foo.get("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = foo.post("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = foo.put("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = foo.patch("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = foo.delete("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        // Bar Tests
        mock = bar.get("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = bar.post("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = bar.put("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = bar.patch("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        mock = bar.delete("string", 123L, List.of("string in array")).get();
        write(mock.getResult());

        // General Tests
        try {
            @SuppressWarnings("unchecked")
            Map<String, Object> result = (Map<String, Object>) general.redirect().get();
            write((String) result.get("result"));
        } catch (Exception e) {
            write(e.getMessage());
        }

        // Upload Tests
        try {
            mock = general.upload("string", 123L, List.of("string in array"),
                InputFile.fromPath("../../resources/file.png"), null).get();
            write(mock.getResult());
        } catch (Exception e) {
            write(e.getCause() != null ? e.getCause().getMessage() : e.getMessage());
        }

        try {
            mock = general.upload("string", 123L, List.of("string in array"),
                InputFile.fromPath("../../resources/large_file.mp4"), null).get();
            write(mock.getResult());
        } catch (Exception e) {
            write(e.getCause() != null ? e.getCause().getMessage() : e.getMessage());
        }

        try {
            byte[] bytes = Files.readAllBytes(Paths.get("../../resources/file.png"));
            mock = general.upload("string", 123L, List.of("string in array"),
                InputFile.fromBytes(bytes, "file.png", "image/png"), null).get();
            write(mock.getResult());
        } catch (Exception e) {
            write(e.getCause() != null ? e.getCause().getMessage() : e.getMessage());
        }

        try {
            byte[] bytes = Files.readAllBytes(Paths.get("../../resources/large_file.mp4"));
            mock = general.upload("string", 123L, List.of("string in array"),
                InputFile.fromBytes(bytes, "large_file.mp4", "video/mp4"), null).get();
            write(mock.getResult());
        } catch (Exception e) {
            write(e.getCause() != null ? e.getCause().getMessage() : e.getMessage());
        }

        // Enum Test
        mock = general.enumTest(MockType.FIRST).get();
        write(mock.getResult());

        // Model Tests
        mock = general.createPlayer(new Player("player1", "John Doe", 100L)).get();
        write(mock.getResult());

        mock = general.createPlayers(List.of(
            new Player("player1", "John Doe", 100L),
            new Player("player2", "Jane Doe", 200L)
        )).get();
        write(mock.getResult());

        // Exception Tests
        try {
            general.error400().get();
        } catch (ExecutionException e) {
            Throwable cause = e.getCause() != null ? e.getCause().getCause() : null;
            if (cause instanceof AppwriteException) {
                AppwriteException ex = (AppwriteException) cause;
                write(ex.getMessage());
                write(ex.getResponse());
            } else {
                write(e.getMessage());
            }
        }

        try {
            general.error500().get();
        } catch (ExecutionException e) {
            Throwable cause = e.getCause() != null ? e.getCause().getCause() : null;
            if (cause instanceof AppwriteException) {
                AppwriteException ex = (AppwriteException) cause;
                write(ex.getMessage());
                write(ex.getResponse());
            } else {
                write(e.getMessage());
            }
        }

        try {
            general.error502().get();
        } catch (ExecutionException e) {
            Throwable cause = e.getCause() != null ? e.getCause().getCause() : null;
            if (cause instanceof AppwriteException) {
                AppwriteException ex = (AppwriteException) cause;
                write(ex.getMessage());
                write(ex.getResponse());
            } else {
                write(e.getMessage());
            }
        }

        try {
            client.setEndpoint("htp://cloud.appwrite.io/v1");
        } catch (IllegalArgumentException e) {
            write(e.getMessage());
        }

        general.empty().get();

        // OAuth2
        String url = (String) general.oauth2("clientId", List.of("test"), "123456",
            "https://localhost", "https://localhost").get();
        write(url);

        // Query helper tests
        write(Query.equal("released", List.of(true)));
        write(Query.equal("title", List.of("Spiderman", "Dr. Strange")));
        write(Query.notEqual("title", "Spiderman"));
        write(Query.lessThan("releasedYear", 1990));
        write(Query.greaterThan("releasedYear", 1990));
        write(Query.search("name", "john"));
        write(Query.isNull("name"));
        write(Query.isNotNull("name"));
        write(Query.between("age", 50, 100));
        write(Query.between("age", 50.5, 100.5));
        write(Query.between("name", "Anna", "Brad"));
        write(Query.startsWith("name", "Ann"));
        write(Query.endsWith("name", "nne"));
        write(Query.select(List.of("name", "age")));
        write(Query.orderAsc("title"));
        write(Query.orderDesc("title"));
        write(Query.orderRandom());
        write(Query.cursorAfter("my_movie_id"));
        write(Query.cursorBefore("my_movie_id"));
        write(Query.limit(50));
        write(Query.offset(20));
        write(Query.contains("title", List.of("Spider")));
        write(Query.contains("labels", List.of("first")));
        write(Query.containsAny("labels", List.of("first", "second")));
        write(Query.containsAll("labels", List.of("first", "second")));
        write(Query.notContains("title", List.of("Spider")));
        write(Query.notSearch("name", "john"));
        write(Query.notBetween("age", 50, 100));
        write(Query.notStartsWith("name", "Ann"));
        write(Query.notEndsWith("name", "nne"));
        write(Query.createdBefore("2023-01-01"));
        write(Query.createdAfter("2023-01-01"));
        write(Query.createdBetween("2023-01-01", "2023-12-31"));
        write(Query.updatedBefore("2023-01-01"));
        write(Query.updatedAfter("2023-01-01"));
        write(Query.updatedBetween("2023-01-01", "2023-12-31"));

        // Spatial Distance queries
        write(Query.distanceEqual("location", List.of(List.of(40.7128, -74), List.of(40.7128, -74)), 1000));
        write(Query.distanceEqual("location", List.of(40.7128, -74), 1000, true));
        write(Query.distanceNotEqual("location", List.of(40.7128, -74), 1000));
        write(Query.distanceNotEqual("location", List.of(40.7128, -74), 1000, true));
        write(Query.distanceGreaterThan("location", List.of(40.7128, -74), 1000));
        write(Query.distanceGreaterThan("location", List.of(40.7128, -74), 1000, true));
        write(Query.distanceLessThan("location", List.of(40.7128, -74), 1000));
        write(Query.distanceLessThan("location", List.of(40.7128, -74), 1000, true));

        // Spatial queries
        write(Query.intersects("location", List.of(40.7128, -74)));
        write(Query.notIntersects("location", List.of(40.7128, -74)));
        write(Query.crosses("location", List.of(40.7128, -74)));
        write(Query.notCrosses("location", List.of(40.7128, -74)));
        write(Query.overlaps("location", List.of(40.7128, -74)));
        write(Query.notOverlaps("location", List.of(40.7128, -74)));
        write(Query.touches("location", List.of(40.7128, -74)));
        write(Query.notTouches("location", List.of(40.7128, -74)));
        write(Query.contains("location", List.of(List.of(40.7128, -74), List.of(40.7128, -74))));
        write(Query.notContains("location", List.of(List.of(40.7128, -74), List.of(40.7128, -74))));
        write(Query.equal("location", List.of(List.of(40.7128, -74), List.of(40.7128, -74))));
        write(Query.notEqual("location", List.of(List.of(40.7128, -74), List.of(40.7128, -74))));

        write(Query.or(List.of(Query.equal("released", List.of(true)), Query.lessThan("releasedYear", 1990))));
        write(Query.and(List.of(Query.equal("released", List.of(false)), Query.greaterThan("releasedYear", 2015))));

        write(Query.regex("name", "pattern.*"));
        write(Query.exists(List.of("attr1", "attr2")));
        write(Query.notExists(List.of("attr1", "attr2")));
        write(Query.elemMatch("friends", List.of(
            Query.equal("name", "Alice"),
            Query.greaterThan("age", 18)
        )));

        // Permission & Role helper tests
        write(Permission.read(Role.any()));
        write(Permission.write(Role.user(ID.custom("userid"))));
        write(Permission.create(Role.users()));
        write(Permission.update(Role.guests()));
        write(Permission.delete(Role.team("teamId", "owner")));
        write(Permission.delete(Role.team("teamId")));
        write(Permission.create(Role.member("memberId")));
        write(Permission.update(Role.users("verified")));
        write(Permission.update(Role.user(ID.custom("userid"), "unverified")));
        write(Permission.create(Role.label("admin")));

        // ID helper tests
        write(ID.unique());
        write(ID.custom("custom_id"));

        // Operator helper tests
        write(Operator.increment(1));
        write(Operator.increment(5, 100));
        write(Operator.decrement(1));
        write(Operator.decrement(3, 0));
        write(Operator.multiply(2));
        write(Operator.multiply(3, 1000));
        write(Operator.divide(2));
        write(Operator.divide(4, 1));
        write(Operator.modulo(5));
        write(Operator.power(2));
        write(Operator.power(3, 100));
        write(Operator.arrayAppend(List.of("item1", "item2")));
        write(Operator.arrayPrepend(List.of("first", "second")));
        write(Operator.arrayInsert(0, "newItem"));
        write(Operator.arrayRemove("oldItem"));
        write(Operator.arrayUnique());
        write(Operator.arrayIntersect(List.of("a", "b", "c")));
        write(Operator.arrayDiff(List.of("x", "y")));
        write(Operator.arrayFilter(Operator.Condition.EQUAL, "test"));
        write(Operator.stringConcat("suffix"));
        write(Operator.stringReplace("old", "new"));
        write(Operator.toggle());
        write(Operator.dateAddDays(7));
        write(Operator.dateSubDays(3));
        write(Operator.dateSetNow());

        out.flush();
        out.close();

        // Print result to stdout
        Files.readAllLines(Paths.get("result.txt")).forEach(System.out::println);
    }

    private static void write(String line) {
        out.println(line != null ? line : "");
    }
}
