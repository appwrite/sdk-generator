from appwrite.client import Client
from appwrite.services.locale import Locale

client = Client()

(client
  .set_project('5df5acd0d48c2') # Your project ID
)

locale = Locale(client)

result = locale.get_currencies()
