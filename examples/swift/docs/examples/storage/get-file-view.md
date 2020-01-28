/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

storage: Storage =  Storage(client);

result = storage.getFileView("[FILE_ID]");
