/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

account: Account =  Account(client);

result = account.updateEmail("email@example.com", "password");
