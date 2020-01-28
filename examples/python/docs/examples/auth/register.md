from appwrite.client import Client
from appwrite.services.auth import Auth

client = Client()

client
    .set_project('')

auth = Auth(client)

result = auth.register('email@example.com', 'password', 'https://example.com')
