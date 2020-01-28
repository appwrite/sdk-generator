from appwrite.client import Client
from appwrite.services.projects import Projects

client = Client()

client
    .set_project('')

projects = Projects(client)

result = projects.update_project_o_auth('[PROJECT_ID]', 'bitbucket')
