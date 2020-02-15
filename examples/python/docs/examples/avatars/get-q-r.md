from appwrite.client import Client
from appwrite.services.avatars import Avatars

client = Client()

(client
  .set_project('5df5acd0d48c2') # Your project ID
)

avatars = Avatars(client)

result = avatars.get_q_r('[TEXT]')
