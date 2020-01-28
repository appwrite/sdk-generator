from appwrite.client import Client
from appwrite.services.teams import Teams

client = Client()

client
    .set_project('')

teams = Teams(client)

result = teams.create_team_membership_resend('[TEAM_ID]', '[INVITE_ID]', 'https://example.com')
