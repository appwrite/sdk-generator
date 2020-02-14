from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

account = Account(client)

result = account.create_session('email@example.com', 'password')
