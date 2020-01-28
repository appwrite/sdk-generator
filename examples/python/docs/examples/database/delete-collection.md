from appwrite.client import Client
from appwrite.services.database import Database

client = Client()

client
    .set_project('')

database = Database(client)

result = database.delete_collection('[COLLECTION_ID]')
