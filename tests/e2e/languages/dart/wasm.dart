import 'dart:convert';

import '../lib/models.dart';
import '../lib/packageName.dart';

// Runs the SDK compiled with `dart compile wasm`, so it takes the browser client.
void main() async {
  final client = Client();
  final foo = Foo(client);
  final bar = Bar(client);

  print('\nTest Started');
  final sdkHeaders = client.getHeaders();
  print("x-sdk-name: ${sdkHeaders['x-sdk-name']}; x-sdk-platform: ${sdkHeaders['x-sdk-platform']}; x-sdk-language: ${sdkHeaders['x-sdk-language']}; x-sdk-version: ${sdkHeaders['x-sdk-version']}");

  client.setProject('123456');
  print(jsonDecode(await client.ping())['result']);
  client.setProject('console');

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
}
