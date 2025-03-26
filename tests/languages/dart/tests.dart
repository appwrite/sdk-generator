import 'dart:convert';
import 'dart:io';

import '../lib/enums.dart';
import '../lib/models.dart';
import '../lib/packageName.dart';
import '../lib/src/input_file.dart';

void main() async {
  Client client = Client().setSelfSigned();
  Foo foo = Foo(client);
  Bar bar = Bar(client);
  General general = General(client);

  client.addHeader('Origin', 'http://localhost');
  client.setSelfSigned();

  print('\nTest Started');

  // Ping pong test
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

  response = await bar.get(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar.post(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar.put(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar.patch(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.result);

  response = await bar.delete(xrequired: 'string', xdefault: 123, z: ['string in array']);
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

  await general.empty();

  final url = await general.oauth2(
      clientId: 'clientId',
      scopes: ['test'],
      state: '123456',
      success: 'https://localhost',
      failure: 'https://localhost'
  );
  print(url);

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
  print(Query.cursorAfter("my_movie_id"));
  print(Query.cursorBefore("my_movie_id"));
  print(Query.limit(50));
  print(Query.offset(20));
  print(Query.contains("title", "Spider"));
  print(Query.contains("labels", "first"));
  print(Query.or([
    Query.equal("released", true),
    Query.lessThan("releasedYear", 1990)
  ]));
  print(Query.and([
    Query.equal("released", false),
    Query.greaterThan("releasedYear", 2015)
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
