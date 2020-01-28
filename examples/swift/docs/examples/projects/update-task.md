/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

projects: Projects =  Projects(client);

result = projects.updateTask("[PROJECT_ID]", "[TASK_ID]", "[NAME]", "play", "", 0, "GET", "https://example.com");
