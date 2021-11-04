import 'package:dart_appwrite/dart_appwrite.dart';

void main() { // Init SDK
  Client client = Client();
  Storage storage = Storage(client);

  client
    .setEndpoint('https://[HOSTNAME_OR_IP]/v1') // Your API Endpoint
    .setProject('5df5acd0d48c2') // Your project ID
    .setKey('919c2d18fb5d4...a2ae413da83346ad2') // Your secret API key
  ;

  Future result = storage.updateFile(
    fileId: '[FILE_ID]',
    read: [],
    write: [],
  );

  result
    .then((response) {
      print(response);
    }).catchError((error) {
      print(error.response);
  });
}