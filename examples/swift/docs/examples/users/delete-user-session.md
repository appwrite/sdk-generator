/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

users: Users =  Users(client);

result = users.deleteUserSession("[USER_ID]", "[SESSION_ID]");
