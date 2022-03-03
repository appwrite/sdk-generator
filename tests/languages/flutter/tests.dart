import 'package:flutter/material.dart';
import '../lib/packageName.dart';
import '../lib/models.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  Client client = Client();
  Foo foo = Foo(client);
  Bar bar = Bar(client);
  General general = General(client);

  client.setSelfSigned();
  client.setProject('console');
  client.setEndPointRealtime(
      "wss://demo.appwrite.io/v1"); // change this later to appwrite.io

  Realtime realtime = Realtime(client);
  final rtsub = realtime.subscribe(["tests"]);

  await Future.delayed(Duration(seconds: 5));
  client.addHeader('Origin', 'http://localhost');
  // Foo Tests
  print('\nTest Started');

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

  var file = InputFile(path: '../../resources/file.png', filename: 'file.png');
  response = await general.upload(
      x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.result);

  file = InputFile(path: '../../resources/large_file.mp4', filename: 'large_file.mp4');
  response = await general.upload(
      x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.result);

  try {
    await general.error400();
  } on AppwriteException catch (e) {
    print(e.message);
  }

  try {
    await general.error500();
  } on AppwriteException catch (e) {
    print(e.message);
  }

  try {
    await general.error502();
  } on AppwriteException catch (e) {
    print(e.message);
  }

  rtsub.stream.listen((message) {
    print(message.payload["response"]);
    rtsub.close();
  });

  await Future.delayed(Duration(seconds: 5));

  // response = await general.setCookie();
  // print(response.result);

  // response = await general.getCookie();
  // print(response.result);
}
