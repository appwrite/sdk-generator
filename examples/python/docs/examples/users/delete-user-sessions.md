from appwrite.client import Client
from appwrite.services.users import Users

client = Client()

(client
  .set_project('')
)

users = Users(client)

result = users.delete_user_sessions('[USER_ID]')
