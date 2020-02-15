from appwrite.client import Client
from appwrite.services.teams import Teams

client = Client()

(client
  .set_project('5df5acd0d48c2') # Your project ID
)

teams = Teams(client)

result = teams.get('[TEAM_ID]')
