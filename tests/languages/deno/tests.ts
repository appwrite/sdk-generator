import * as appwrite from "../../sdks/deno/mod.ts";

// Local helper type to reflect the shape the test expects
type SDKResponse<T = unknown> = { result: T };

// Hardened error logger (safe for unknowns, includes stack when available)
function logError(e: unknown) {
  if (e instanceof Error) {
    console.error(e.message);
    // Appwrite errors often carry a `response` payload
    const resp = (e as Record<string, unknown>)["response"];
    if (resp !== undefined) console.error(resp);
    if (e.stack) console.error(e.stack);
    return;
  }
  if (typeof e === "object" && e !== null) {
    const obj = e as Record<string, unknown>;
    const msg = typeof obj["message"] === "string"
      ? (obj["message"] as string)
      : "";
    if (msg) console.error(msg);
    if ("response" in obj) console.error(obj["response"]);
    else console.error(obj);
  } else {
    console.error(String(e));
  }
}

async function start() {
  let response: SDKResponse;

  const Permission = appwrite.Permission;
  const Role = appwrite.Role;
  const ID = appwrite.ID;
  const Query = appwrite.Query;

  // Init SDK
  const client = new appwrite.Client().addHeader("Origin", "http://localhost");

  const foo = new appwrite.Foo(client);
  const bar = new appwrite.Bar(client);
  const general = new appwrite.General(client);

  console.log("\nTest Started");

  // Foo
  response = await foo.get("string", 123, ["string in array"]);
  console.log(response.result);

  response = await foo.post("string", 123, ["string in array"]);
  console.log(response.result);

  response = await foo.put("string", 123, ["string in array"]);
  console.log(response.result);

  response = await foo.patch("string", 123, ["string in array"]);
  console.log(response.result);

  response = await foo.delete("string", 123, ["string in array"]);
  console.log(response.result);

  // Bar
  response = await bar.get("string", 123, ["string in array"]);
  console.log(response.result);

  response = await bar.post("string", 123, ["string in array"]);
  console.log(response.result);

  response = await bar.put("string", 123, ["string in array"]);
  console.log(response.result);

  response = await bar.patch("string", 123, ["string in array"]);
  console.log(response.result);

  response = await bar.delete("string", 123, ["string in array"]);
  console.log(response.result);

  response = await general.redirect();
  console.log(response.result);

  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromPath("./tests/resources/file.png", "file.png"),
  );
  console.log(response.result);

  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromPath(
      "./tests/resources/large_file.mp4",
      "large_file.mp4",
    ),
  );
  console.log(response.result);

  let buffer = await Deno.readFile("./tests/resources/file.png");
  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromBuffer(buffer, "file.png"),
  );
  console.log(response.result);

  buffer = await Deno.readFile("./tests/resources/large_file.mp4");
  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromBuffer(buffer, "large_file.mp4"),
  );
  console.log(response.result);

  response = await general.enum(appwrite.MockType.First);
  console.log(response.result);

  try {
    response = await general.error400();
  } catch (error: unknown) {
    logError(error);
  }

  try {
    response = await general.error500();
  } catch (error: unknown) {
    logError(error);
  }

  try {
    response = await general.error502();
  } catch (error: unknown) {
    logError(error);
  }

  try {
    client.setEndpoint("htp://cloud.appwrite.io/v1"); // intentional: trigger error for test
  } catch (error: unknown) {
    logError(error);
  }

  await general.empty();

  const url = await general.oauth2(
    "clientId",
    ["test"],
    "123456",
    "https://localhost",
    "https://localhost",
  );
  console.log(url);

  // Query helper tests
  console.log(Query.equal("released", [true]));
  console.log(Query.equal("title", ["Spiderman", "Dr. Strange"]));
  console.log(Query.notEqual("title", "Spiderman"));
  console.log(Query.lessThan("releasedYear", 1990));
  console.log(Query.greaterThan("releasedYear", 1990));
  console.log(Query.search("name", "john"));
  console.log(Query.isNull("name"));
  console.log(Query.isNotNull("name"));
  console.log(Query.between("age", 50, 100));
  console.log(Query.between("age", 50.5, 100.5));
  console.log(Query.between("name", "Anna", "Brad"));
  console.log(Query.startsWith("name", "Ann"));
  console.log(Query.endsWith("name", "nne"));
  console.log(Query.select(["name", "age"]));
  console.log(Query.orderAsc("title"));
  console.log(Query.orderDesc("title"));
  console.log(Query.cursorAfter("my_movie_id"));
  console.log(Query.cursorBefore("my_movie_id"));
  console.log(Query.limit(50));
  console.log(Query.offset(20));
  console.log(Query.contains("title", "Spider"));
  console.log(Query.contains("labels", "first"));

  // New query methods
  console.log(Query.notContains("title", "Spider"));
  console.log(Query.notSearch("name", "john"));
  console.log(Query.notBetween("age", 50, 100));
  console.log(Query.notStartsWith("name", "Ann"));
  console.log(Query.notEndsWith("name", "nne"));
  console.log(Query.createdBefore("2023-01-01"));
  console.log(Query.createdAfter("2023-01-01"));
  console.log(Query.updatedBefore("2023-01-01"));
  console.log(Query.updatedAfter("2023-01-01"));

  console.log(
    Query.or([
      Query.equal("released", true),
      Query.lessThan("releasedYear", 1990),
    ]),
  );
  console.log(
    Query.and([
      Query.equal("released", false),
      Query.greaterThan("releasedYear", 2015),
    ]),
  );

  // Permission & Role helper tests
  console.log(Permission.read(Role.any()));
  console.log(Permission.write(Role.user(ID.custom("userid"))));
  console.log(Permission.create(Role.users()));
  console.log(Permission.update(Role.guests()));
  console.log(Permission.delete(Role.team("teamId", "owner")));
  console.log(Permission.delete(Role.team("teamId")));
  console.log(Permission.create(Role.member("memberId")));
  console.log(Permission.update(Role.users("verified")));
  console.log(Permission.update(Role.user(ID.custom("userid"), "unverified")));
  console.log(Permission.create(Role.label("admin")));

  // ID helper tests
  console.log(ID.unique());
  console.log(ID.custom("custom_id"));

  response = await general.headers();
  console.log(response.result);
}

start().catch((err) => logError(err));
