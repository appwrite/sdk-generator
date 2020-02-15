from appwrite.client import Client
from appwrite.services.avatars import Avatars

client = Client()

(client
  .set_project('5df5acd0d48c2')
)

avatars = Avatars(client)

result = avatars.get_favicon('https://example.com')
