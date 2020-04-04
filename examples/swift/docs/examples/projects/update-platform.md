/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var projects: Projects =  Projects(client: client);

var result = projects.updatePlatform(_projectId: "[PROJECT_ID]", _platformId: "[PLATFORM_ID]", _name: "[NAME]");
