from appwrite.client import Client
from appwrite.services.avatars import Avatars

client = Client()

client
    .set_project('')

avatars = Avatars(client)

result = avatars.get_q_r('[TEXT]')
