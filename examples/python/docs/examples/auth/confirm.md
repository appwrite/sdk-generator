from appwrite.client import Client
from appwrite.services.auth import Auth

client = Client()

auth = Auth(client)

result = auth.confirm('[USER_ID]', '[TOKEN]')
