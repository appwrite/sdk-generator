/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

database: Database =  Database(client);

result = database.getDocument("[COLLECTION_ID]", "[DOCUMENT_ID]");
