from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

(client
  .set_project('5df5acd0d48c2') # Your project ID
)

account = Account(client)

result = account.create_o_auth_session('bitbucket', 'https://example.com', 'https://example.com')
