from appwrite.client import Client
from appwrite.services.storage import Storage

client = Client()

(client
  .set_project('5df5acd0d48c2')
)

storage = Storage(client)

result = storage.update_file('[FILE_ID]', {}, {})
