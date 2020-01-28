/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

database: Database =  Database(client);

result = database.updateDocument("[COLLECTION_ID]", "[DOCUMENT_ID]", "{}", [], []);
