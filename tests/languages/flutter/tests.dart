import '../lib/packageName.dart';

void main() async {
  Client client = Client();
  Foo foo = Foo(client);
  Bar bar = Bar(client);
  General general = General(client);

  client.setSelfSigned();

  await Future.delayed(Duration(seconds: 4));
  client.addHeader('Origin', 'http://localhost');
  // Foo Tests
  print('\nTest Started');

  Response response;
  response = await foo.get(x: 'string', y: 123, z: ['string in array']);
  print(response.data['result']);

  response = await foo.post(x: 'string', y: 123, z: ['string in array']);
  print(response.data['result']);

  response = await foo.put(x: 'string', y: 123, z: ['string in array']);
  print(response.data['result']);

  response = await foo.patch(x: 'string', y: 123, z: ['string in array']);
  print(response.data['result']);

  response = await foo.delete(x: 'string', y: 123, z: ['string in array']);
  print(response.data['result']);

  // Bar Tests

  response = await bar.get(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.data['result']);

  response = await bar.post(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.data['result']);

  response = await bar.put(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.data['result']);

  response = await bar.patch(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.data['result']);

  response = await bar.delete(xrequired: 'string', xdefault: 123, z: ['string in array']);
  print(response.data['result']);

  // General Tests

  response = await general.redirect();
  print(response.data['result']);

  final file = await MultipartFile.fromPath('file', '../../resources/file.png',
      filename: 'file.png');
  response = await general.upload(
      x: 'string', y: 123, z: ['string in array'], file: file);
  print(response.data['result']);

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

  // response = await general.setCookie();
  // print(response.data['result']);

  // response = await general.getCookie();
  // print(response.data['result']);
}
