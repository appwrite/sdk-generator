from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

account = Account(client)

result = account.create_o_auth_session('bitbucket', 'https://example.com', 'https://example.com')
