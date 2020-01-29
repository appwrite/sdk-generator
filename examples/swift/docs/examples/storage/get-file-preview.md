/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var storage: Storage =  Storage(client: client);

var result = storage.getFilePreview(_fileId: "[FILE_ID]");
