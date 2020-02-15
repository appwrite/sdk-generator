from appwrite.client import Client
from appwrite.services.storage import Storage

client = Client()

(client
  .set_project('5df5acd0d48c2') # Your project ID
)

storage = Storage(client)

result = storage.get_file_preview('[FILE_ID]')
