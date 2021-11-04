import 'package:dart_appwrite/dart_appwrite.dart';

void main() { // Init SDK
  Client client = Client();
  Functions functions = Functions(client);

  client
    .setEndpoint('https://[HOSTNAME_OR_IP]/v1') // Your API Endpoint
    .setProject('5df5acd0d48c2') // Your project ID
    .setKey('919c2d18fb5d4...a2ae413da83346ad2') // Your secret API key
  ;

  Future result = functions.createTag(
    functionId: '[FUNCTION_ID]',
    command: '[COMMAND]',
    code: await MultipartFile.fromPath('code', './path-to-files/image.jpg', 'image.jpg'),
  );

  result
    .then((response) {
      print(response);
    }).catchError((error) {
      print(error.response);
  });
}