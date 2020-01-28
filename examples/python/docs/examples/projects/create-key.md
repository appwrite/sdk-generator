from appwrite.client import Client
from appwrite.services.projects import Projects

client = Client()

client
    .set_project('')

projects = Projects(client)

result = projects.create_key('[PROJECT_ID]', '[NAME]', {})
