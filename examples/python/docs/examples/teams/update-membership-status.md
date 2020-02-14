from appwrite.client import Client
from appwrite.services.teams import Teams

client = Client()

teams = Teams(client)

result = teams.update_membership_status('[TEAM_ID]', '[INVITE_ID]', '[USER_ID]', '[SECRET]')
