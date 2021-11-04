from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

(client
  .set_endpoint('https://[HOSTNAME_OR_IP]/v1') # Your API Endpoint
  .set_project('5df5acd0d48c2') # Your project ID
)

account = Account(client)

result = account.create('email@example.com', 'password')
