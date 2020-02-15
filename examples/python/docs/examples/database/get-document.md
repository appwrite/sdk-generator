from appwrite.client import Client
from appwrite.services.database import Database

client = Client()

(client
  .set_project('5df5acd0d48c2')
)

database = Database(client)

result = database.get_document('[COLLECTION_ID]', '[DOCUMENT_ID]')
