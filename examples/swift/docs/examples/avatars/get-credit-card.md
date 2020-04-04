/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var avatars: Avatars =  Avatars(client: client);

var result = avatars.getCreditCard(_code: "amex");
