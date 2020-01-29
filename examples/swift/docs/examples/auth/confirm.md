/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setEndpoint(endpoint: "http://localhost/v1")

var auth: Auth =  Auth(client: client);

var result = auth.confirm(_userId: "[USER_ID]", _token: "[TOKEN]");
