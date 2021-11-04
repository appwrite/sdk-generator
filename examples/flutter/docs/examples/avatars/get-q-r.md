import 'package:appwrite/appwrite.dart';

void main() { // Init SDK
  Client client = Client();
  Avatars avatars = Avatars(client);

  client
    .setEndpoint('https://[HOSTNAME_OR_IP]/v1') // Your API Endpoint
    .setProject('5df5acd0d48c2') // Your project ID
    .setKey('919c2d18fb5d4...a2ae413da83346ad2') // Your secret API key
  ;
}

//displaying image
FutureBuilder(
  future: avatars.getQR(
    text: '[TEXT]',
  ), //works for both public file and private file, for private files you need to be logged in
  builder: (context, snapshot) {
    return snapshot.hasData && snapshot.data != null
      ? Image.memory(
          snapshot.data,
        )
      : CircularProgressIndicator();
  },
);
