from appwrite.client import Client
from appwrite.services.database import Database

client = Client()

client
    .set_project('')

database = Database(client)

result = database.create_collection('[NAME]', {}, {}, {})
