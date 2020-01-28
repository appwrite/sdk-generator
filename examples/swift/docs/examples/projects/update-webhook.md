/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

projects: Projects =  Projects(client);

result = projects.updateWebhook("[PROJECT_ID]", "[WEBHOOK_ID]", "[NAME]", [], "[URL]", 0);
