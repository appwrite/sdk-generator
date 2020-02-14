from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

(client
  .set_project('')
)

account = Account(client)

result = account.create_verification('https://example.com')
