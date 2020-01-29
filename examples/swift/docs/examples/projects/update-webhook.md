/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var projects: Projects =  Projects(client: client);

var result = projects.updateWebhook(_projectId: "[PROJECT_ID]", _webhookId: "[WEBHOOK_ID]", _name: "[NAME]", _events: [], _url: "[URL]", _security: 0);
