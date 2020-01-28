from appwrite.client import Client
from appwrite.services.projects import Projects

client = Client()

(client
  .set_project('')
)

projects = Projects(client)

result = projects.update_task('[PROJECT_ID]', '[TASK_ID]', '[NAME]', 'play', '', 0, 'GET', 'https://example.com')
