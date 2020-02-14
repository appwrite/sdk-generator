from appwrite.client import Client
from appwrite.services.teams import Teams

client = Client()

(client
  .set_project('')
)

teams = Teams(client)

result = teams.create_membership('[TEAM_ID]', 'email@example.com', {}, 'https://example.com')
