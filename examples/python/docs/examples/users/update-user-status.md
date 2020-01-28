from appwrite.client import Client
from appwrite.services.users import Users

client = Client()

client
    .set_project('')

users = Users(client)

result = users.update_user_status('[USER_ID]', '1')
