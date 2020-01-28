/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

auth: Auth =  Auth(client);

result = auth.oauth("bitbucket", "https://example.com", "https://example.com");
