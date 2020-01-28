from appwrite.client import Client
from appwrite.services.users import Users

client = Client()

client
    .set_project('')

users = Users(client)

result = users.delete_user_session('[USER_ID]', '[SESSION_ID]')
