from appwrite.client import Client
from appwrite.services.projects import Projects

client = Client()

client
    .set_project('')

projects = Projects(client)

result = projects.delete_webhook('[PROJECT_ID]', '[WEBHOOK_ID]')
