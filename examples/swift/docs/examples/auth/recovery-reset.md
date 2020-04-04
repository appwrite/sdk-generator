/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var auth: Auth =  Auth(client: client);

var result = auth.recoveryReset(_userId: "[USER_ID]", _token: "[TOKEN]", _passwordA: "password", _passwordB: "password");
