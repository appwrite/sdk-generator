/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

storage: Storage =  Storage(client);

result = storage.updateFile("[FILE_ID]", [], []);
