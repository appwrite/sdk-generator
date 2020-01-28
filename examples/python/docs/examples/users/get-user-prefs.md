from appwrite.client import Client
from appwrite.services.users import Users

client = Client()

client
    .set_project('')

users = Users(client)

result = users.get_user_prefs('[USER_ID]')
