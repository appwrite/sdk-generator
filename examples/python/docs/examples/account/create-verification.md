from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

(client
  .set_project('5df5acd0d48c2')
)

account = Account(client)

result = account.create_verification('https://example.com')
