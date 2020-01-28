/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

users: Users =  Users(client);

result = users.getUserPrefs("[USER_ID]");
