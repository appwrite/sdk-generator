/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

projects: Projects =  Projects(client);

result = projects.createPlatform("[PROJECT_ID]", "web", "[NAME]");
