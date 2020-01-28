/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

avatars: Avatars =  Avatars(client);

result = avatars.getImage("https://example.com");
