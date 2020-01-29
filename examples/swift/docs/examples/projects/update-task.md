/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var projects: Projects =  Projects(client: client);

var result = projects.updateTask(_projectId: "[PROJECT_ID]", _taskId: "[TASK_ID]", _name: "[NAME]", _status: "play", _schedule: "", _security: 0, _httpMethod: "GET", _httpUrl: "https://example.com");
