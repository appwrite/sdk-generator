from appwrite.client import Client
from appwrite.services.database import Database

client = Client()

(client
  .set_project('5df5acd0d48c2') # Your project ID
)

database = Database(client)

result = database.update_document('[COLLECTION_ID]', '[DOCUMENT_ID]', {}, {}, {})
