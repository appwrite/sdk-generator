from appwrite.client import Client
from appwrite.services.account import Account

client = Client()

(client
  .set_project('')
)

account = Account(client)

result = account.update_name('[NAME]')
