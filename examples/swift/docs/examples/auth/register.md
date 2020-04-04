/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var auth: Auth =  Auth(client: client);

var result = auth.register(_email: "email@example.com", _password: "password", _confirm: "https://example.com");
