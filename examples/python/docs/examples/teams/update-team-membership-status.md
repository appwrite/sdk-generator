from appwrite.client import Client
from appwrite.services.teams import Teams

client = Client()

client
    .set_project('')

teams = Teams(client)

result = teams.update_team_membership_status('[TEAM_ID]', '[INVITE_ID]', '[USER_ID]', '[SECRET]')
