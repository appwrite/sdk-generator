from appwrite.client import Client
from appwrite.services.auth import Auth

client = Client()

(client
  .set_project('')
)

auth = Auth(client)

result = auth.oauth('bitbucket', 'https://example.com', 'https://example.com')
