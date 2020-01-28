/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

auth: Auth =  Auth(client);

result = auth.register("email@example.com", "password", "https://example.com");
