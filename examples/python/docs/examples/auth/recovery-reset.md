from appwrite.client import Client
from appwrite.services.auth import Auth

client = Client()

client
    .set_project('')

auth = Auth(client)

result = auth.recovery_reset('[USER_ID]', '[TOKEN]', 'password', 'password')
