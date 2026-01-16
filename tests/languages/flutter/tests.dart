import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:path_provider_platform_interface/path_provider_platform_interface.dart';

import '../lib/client_io.dart';
import '../lib/enums.dart';
import '../lib/models.dart';
import '../lib/packageName.dart';
import '../lib/src/input_file.dart';

class FakePathProvider extends PathProviderPlatform {
  @override
  Future<String?> getApplicationDocumentsPath() async {
    return '.';
  }
}


void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  PathProviderPlatform.instance = FakePathProvider();
  Client client = Client()
      .setProject('123456')
      .addHeader("Origin", "http://localhost")
      .setSelfSigned();

  Foo foo = Foo(client);
  Bar bar = Bar(client);
  General general = General(client);

  client.setSelfSigned();
  client.setProject('console');
  client.setEndPointRealtime(
      "wss://cloud.appwrite.io/v1");

  Realtime realtime = Realtime(client);
  final rtsub = realtime.subscribe(["tests"]);

  await Future.delayed(Duration(seconds: 5));
  client.addHeader('Origin', 'http://localhost');
  print('\nTest Started');

  // Ping pong tests
  client.setProject('123456');
  final ping = await client.ping();
  final pingResponse = parse(ping)!;
  print(pingResponse);

  // reset project.
  client.setProject('console');

  // Foo Tests
  Mock response;
  response = await foo.get(x: 'string', y: 123, z: ['string in array']);
  print(response.result);

  response = await foo.post(x: 'string', y: 123, z: ['string in array']);
  print(response.result);

  response = await foo.put(x: 'string', y: 123, z: ['string in array']);
  print(response.result);

  response = await foo.patch(x: 'string', y: 123, z: ['string in array']);
  print(response.result);

  response = await foo.delete(x: 'string', y: 123, z: ['string in array']);
  print(response.result);

  // Bar Tests

  response =
      await bar.get(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar
      .post(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response =
      await bar.put(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar
      .patch(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar
      .delete(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  // General Tests

  final res = await general.redirect();
  print(res['result']);

  var file = InputFile.fromPath(path: '../../resources/file.png', filename: 'file.png');
  response = await general.upload(x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.result);

  file = InputFile.fromPath(path: '../../resources/large_file.mp4', filename: 'large_file.mp4');
  response = await general.upload(x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.result);

  var resource = File.fromUri(Uri.parse('../../resources/file.png'));
  var bytes = await resource.readAsBytes();
  file = InputFile.fromBytes(bytes: bytes, filename: 'file.png');
  response = await general.upload(x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.result);

  resource = File.fromUri(Uri.parse('../../resources/large_file.mp4'));
  bytes = await resource.readAsBytes();
  file = InputFile.fromBytes(bytes: bytes, filename: 'large_file.mp4');
  response = await general.upload(x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.result);

  response = await general.xenum(mockType: MockType.first);
  print(response.result);

  // Request model tests
  response = await general.createPlayer(player: Player(id: 'player1', name: 'John Doe', score: 100));
  print(response.result);

  response = await general.createPlayers(players: [
    Player(id: 'player1', name: 'John Doe', score: 100),
    Player(id: 'player2', name: 'Jane Doe', score: 200)
  ]);
  print(response.result);

  try {
    await general.error400();
  } on AppwriteException catch (e) {
    print(e.message);
    print(e.response);
  }

  try {
    await general.error500();
  } on AppwriteException catch (e) {
    print(e.message);
    print(e.response);
  }

  try {
    await general.error502();
  } on AppwriteException catch (e) {
    print(e.message);
    print(e.response);
  }

  try {
    client.setEndpoint("htp://cloud.appwrite.io/v1");
  } on AppwriteException catch (e) {
    print(e.message);
  }

  rtsub.stream.listen((message) {
    print(message.payload["response"]);
    rtsub.close();
  });

  await Future.delayed(Duration(seconds: 5));

  response = await general.setCookie();
  print(response.result);

  response = await general.getCookie();
  print(response.result);

  await general.empty();

  // Query helper tests
  print(Query.equal('released', [true]));
  print(Query.equal('title', ['Spiderman', 'Dr. Strange']));
  print(Query.notEqual('title', 'Spiderman'));
  print(Query.lessThan('releasedYear', 1990));
  print(Query.greaterThan('releasedYear', 1990));
  print(Query.search('name', 'john'));
  print(Query.isNull("name"));
  print(Query.isNotNull("name"));
  print(Query.between("age", 50, 100));
  print(Query.between("age", 50.5, 100.5));
  print(Query.between("name", "Anna", "Brad"));
  print(Query.startsWith("name", "Ann"));
  print(Query.endsWith("name", "nne"));
  print(Query.select(["name", "age"]));
  print(Query.orderAsc("title"));
  print(Query.orderDesc("title"));
  print(Query.orderRandom());
  print(Query.cursorAfter("my_movie_id"));
  print(Query.cursorBefore("my_movie_id"));
  print(Query.limit(50));
  print(Query.offset(20));
  print(Query.contains("title", "Spider"));
  print(Query.contains("labels", "first"));
  
  // New query methods
  print(Query.notContains("title", "Spider"));
  print(Query.notSearch("name", "john"));
  print(Query.notBetween("age", 50, 100));
  print(Query.notStartsWith("name", "Ann"));
  print(Query.notEndsWith("name", "nne"));
  print(Query.createdBefore("2023-01-01"));
  print(Query.createdAfter("2023-01-01"));
  print(Query.createdBetween("2023-01-01", "2023-12-31"));
  print(Query.updatedBefore("2023-01-01"));
  print(Query.updatedAfter("2023-01-01"));
  print(Query.updatedBetween("2023-01-01", "2023-12-31"));
  
  // Spatial Distance query tests
  print(Query.distanceEqual("location", [[40.7128, -74], [40.7128, -74]], 1000));
  print(Query.distanceEqual("location", [40.7128, -74], 1000, true));
  print(Query.distanceNotEqual("location", [40.7128, -74], 1000));
  print(Query.distanceNotEqual("location", [40.7128, -74], 1000, true));
  print(Query.distanceGreaterThan("location", [40.7128, -74], 1000));
  print(Query.distanceGreaterThan("location", [40.7128, -74], 1000, true));
  print(Query.distanceLessThan("location", [40.7128, -74], 1000));
  print(Query.distanceLessThan("location", [40.7128, -74], 1000, true));

  // Spatial query tests
  print(Query.intersects("location", [40.7128, -74]));
  print(Query.notIntersects("location", [40.7128, -74]));
  print(Query.crosses("location", [40.7128, -74]));
  print(Query.notCrosses("location", [40.7128, -74]));
  print(Query.overlaps("location", [40.7128, -74]));
  print(Query.notOverlaps("location", [40.7128, -74]));
  print(Query.touches("location", [40.7128, -74]));
  print(Query.notTouches("location", [40.7128, -74]));
  print(Query.contains("location", [[40.7128, -74], [40.7128, -74]]));
  print(Query.notContains("location", [[40.7128, -74], [40.7128, -74]]));
  print(Query.equal("location", [[40.7128, -74], [40.7128, -74]]));
  print(Query.notEqual("location", [[40.7128, -74], [40.7128, -74]]));
  
  print(Query.or([
    Query.equal("released", true),
    Query.lessThan("releasedYear", 1990)
  ]));
   print(Query.and([
    Query.equal("released", false),
    Query.greaterThan("releasedYear", 2015)
  ]));
  
  // regex, exists, notExists, elemMatch
  print(Query.regex("name", "pattern.*"));
  print(Query.exists(["attr1", "attr2"]));
  print(Query.notExists(["attr1", "attr2"]));
  print(Query.elemMatch("friends", [
    Query.equal("name", "Alice"),
    Query.greaterThan("age", 18)
  ]));

  // Permission & Role helper tests
  print(Permission.read(Role.any()));
  print(Permission.write(Role.user(ID.custom('userid'))));
  print(Permission.create(Role.users()));
  print(Permission.update(Role.guests()));
  print(Permission.delete(Role.team('teamId', 'owner')));
  print(Permission.delete(Role.team('teamId')));
  print(Permission.create(Role.member('memberId')));
  print(Permission.update(Role.users('verified')));
  print(Permission.update(Role.user(ID.custom('userid'), 'unverified')));
  print(Permission.create(Role.label('admin')));

  // ID helper tests
  print(ID.unique());
  print(ID.custom('custom_id'));

  // Channel helper tests
  print(Channel.database().collection().document().toString());
  print(Channel.database('db1').collection('col1').document('doc1').toString());
  print(Channel.database('db1').collection('col1').document('doc1').create().toString());
  print(Channel.tablesdb().table().row().toString());
  print(Channel.tablesdb('db1').table('table1').row('row1').toString());
  print(Channel.tablesdb('db1').table('table1').row('row1').update().toString());
  print(Channel.account());
  print(Channel.account('user123'));
  print(Channel.bucket().file().toString());
  print(Channel.bucket('bucket1').file('file1').toString());
  print(Channel.bucket('bucket1').file('file1').delete().toString());
  print(Channel.function().execution().toString());
  print(Channel.function('func1').execution('exec1').toString());
  print(Channel.function('func1').execution('exec1').create().toString());
  print(Channel.team().toString());
  print(Channel.team('team1').toString());
  print(Channel.team('team1').create().toString());
  print(Channel.membership().toString());
  print(Channel.membership('membership1').toString());
  print(Channel.membership('membership1').update().toString());

  // Operator helper tests
  print(Operator.increment(1));
  print(Operator.increment(5, 100));
  print(Operator.decrement(1));
  print(Operator.decrement(3, 0));
  print(Operator.multiply(2));
  print(Operator.multiply(3, 1000));
  print(Operator.divide(2));
  print(Operator.divide(4, 1));
  print(Operator.modulo(5));
  print(Operator.power(2));
  print(Operator.power(3, 100));
  print(Operator.arrayAppend(["item1", "item2"]));
  print(Operator.arrayPrepend(["first", "second"]));
  print(Operator.arrayInsert(0, "newItem"));
  print(Operator.arrayRemove("oldItem"));
  print(Operator.arrayUnique());
  print(Operator.arrayIntersect(["a", "b", "c"]));
  print(Operator.arrayDiff(["x", "y"]));
  print(Operator.arrayFilter(Condition.equal, "test"));
  print(Operator.stringConcat("suffix"));
  print(Operator.stringReplace("old", "new"));
  print(Operator.toggle());
  print(Operator.dateAddDays(7));
  print(Operator.dateSubDays(3));
  print(Operator.dateSetNow());

  response = await general.headers();
  print(response.result);
}

String? parse(String json) {
  try {
    return jsonDecode(json)['result'] as String?;
  } catch (_) {
    return null;
  }
}