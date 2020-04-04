/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var users: Users =  Users(client: client);

var result = users.getUser(_userId: "[USER_ID]");
