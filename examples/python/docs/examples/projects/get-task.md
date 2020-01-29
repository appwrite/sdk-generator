from appwrite.client import Client
from appwrite.services.projects import Projects

client = Client()

(client
  .set_project('')
)

projects = Projects(client)

result = projects.get_task('[PROJECT_ID]', '[TASK_ID]')
