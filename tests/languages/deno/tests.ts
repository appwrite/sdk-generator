import * as appwrite from "../../sdks/deno/mod.ts";

// TODO: Correct test typings and remove '// @ts-ignore'

async function start() {
  var response;

  let Permission = appwrite.Permission;
  let Role = appwrite.Role;
  let ID = appwrite.ID;
  let Query = appwrite.Query;
  let Operator = appwrite.Operator;

  // Init SDK
  let client = new appwrite.Client().addHeader("Origin", "http://localhost");

  let foo = new appwrite.Foo(client);
  let bar = new appwrite.Bar(client);
  let general = new appwrite.General(client);

  client.addHeader("Origin", "http://localhost");

  console.log("\nTest Started");

  // Foo

  response = await foo.get("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await foo.post("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await foo.put("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await foo.patch("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await foo.delete("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  // Bar

  response = await bar.get("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await bar.post("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await bar.put("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await bar.patch("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await bar.delete("string", 123, ["string in array"]);
  // @ts-ignore
  console.log(response.result);

  response = await general.redirect();
  // @ts-ignore
  console.log(response.result);

  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromPath("./tests/resources/file.png", "file.png")
  );
  // @ts-ignore
  console.log(response.result);

  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromPath(
      "./tests/resources/large_file.mp4",
      "large_file.mp4"
    )
  );
  // @ts-ignore
  console.log(response.result);

  let buffer = await Deno.readFile("./tests/resources/file.png");
  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromBuffer(buffer, "file.png")
  );
  // @ts-ignore
  console.log(response.result);

  buffer = await Deno.readFile("./tests/resources/large_file.mp4");
  response = await general.upload(
    "string",
    123,
    ["string in array"],
    appwrite.InputFile.fromBuffer(buffer, "large_file.mp4")
  );
  // @ts-ignore
  console.log(response.result);

  response = await general.enum(appwrite.MockType.First);
  // @ts-ignore
  console.log(response.result);

  try {
    response = await general.error400();
  } catch (error) {
    console.log(error.message);
    console.log(error.response);
  }

  try {
    response = await general.error500();
  } catch (error) {
    console.log(error.message);
    console.log(error.response);
  }

  try {
    response = await general.error502();
  } catch (error) {
    console.log(error.message);
    console.log(error.response);
  }

  try {
    client.setEndpoint("htp://cloud.appwrite.io/v1");
  } catch (error) {
    console.log(error.message);
  }

  await general.empty();

  const url = await general.oauth2(
      'clientId',
      ['test'],
      '123456',
      'https://localhost',
      'https://localhost'
  )
  console.log(url)

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
  console.log(Query.orderRandom());
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
  console.log(Query.createdBetween("2023-01-01", "2023-12-31"));
  console.log(Query.updatedBefore("2023-01-01"));
  console.log(Query.updatedAfter("2023-01-01"));
  console.log(Query.updatedBetween("2023-01-01", "2023-12-31"));
  
  // Spatial Distance query tests
  console.log(Query.distanceEqual("location", [[40.7128, -74], [40.7128, -74]], 1000));
  console.log(Query.distanceEqual("location", [40.7128, -74], 1000, true));
  console.log(Query.distanceNotEqual("location", [40.7128, -74], 1000));
  console.log(Query.distanceNotEqual("location", [40.7128, -74], 1000, true));
  console.log(Query.distanceGreaterThan("location", [40.7128, -74], 1000));
  console.log(Query.distanceGreaterThan("location", [40.7128, -74], 1000, true));
  console.log(Query.distanceLessThan("location", [40.7128, -74], 1000));
  console.log(Query.distanceLessThan("location", [40.7128, -74], 1000, true));

  // Spatial query tests
  console.log(Query.intersects("location", [40.7128, -74]));
  console.log(Query.notIntersects("location", [40.7128, -74]));
  console.log(Query.crosses("location", [40.7128, -74]));
  console.log(Query.notCrosses("location", [40.7128, -74]));
  console.log(Query.overlaps("location", [40.7128, -74]));
  console.log(Query.notOverlaps("location", [40.7128, -74]));
  console.log(Query.touches("location", [40.7128, -74]));
  console.log(Query.notTouches("location", [40.7128, -74]));
  console.log(Query.contains("location", [[40.7128, -74], [40.7128, -74]]));
  console.log(Query.notContains("location", [[40.7128, -74], [40.7128, -74]]));
  console.log(Query.equal("location", [[40.7128, -74], [40.7128, -74]]));
  console.log(Query.notEqual("location", [[40.7128, -74], [40.7128, -74]]));
  
  console.log(Query.or([
    Query.equal("released", true),
    Query.lessThan("releasedYear", 1990)
  ]));
  console.log(Query.and([
    Query.equal("released", false),
    Query.greaterThan("releasedYear", 2015)
  ]));

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

  // Operator helper tests
  console.log(Operator.increment(1));
  console.log(Operator.increment(5, 100));
  console.log(Operator.decrement(1));
  console.log(Operator.decrement(5, 0));
  console.log(Operator.multiply(2));
  console.log(Operator.divide(2));
  console.log(Operator.modulo(3));
  console.log(Operator.power(2));
  console.log(Operator.append("value"));
  console.log(Operator.append(["value1", "value2"]));
  console.log(Operator.prepend("value"));
  console.log(Operator.prepend(["value1", "value2"]));
  console.log(Operator.insert(0, "value"));
  console.log(Operator.insert(1, ["value1", "value2"]));
  console.log(Operator.remove("value"));
  console.log(Operator.remove(["value1", "value2"]));
  console.log(Operator.unique());
  console.log(Operator.intersect(["value1", "value2"]));
  console.log(Operator.diff(["value1", "value2"]));
  console.log(Operator.filter("value1", "value2"));
  console.log(Operator.concat("newValue"));
  console.log(Operator.replace("oldValue", "newValue"));
  console.log(Operator.toggle());
  console.log(Operator.dateAddDays(7));
  console.log(Operator.dateSubDays(7));
  console.log(Operator.dateSetNow());

  response = await general.headers();
  // @ts-ignore
  console.log(response.result);
}

start().catch((err) => {
  console.error(err);
});
