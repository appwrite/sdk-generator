from appwrite.client import Client
from appwrite.services.projects import Projects

client = Client()

client
    .set_project('')

projects = Projects(client)

result = projects.update_platform('[PROJECT_ID]', '[PLATFORM_ID]', '[NAME]')
