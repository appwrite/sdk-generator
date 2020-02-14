from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

account = Account(client)

result = account.update_verification('[USER_ID]', '[SECRET]')
