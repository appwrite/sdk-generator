from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

account = Account(client)

result = account.create_recovery('email@example.com', 'https://example.com')
