from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

account = Account(client)

result = account.create('email@example.com', 'password')
