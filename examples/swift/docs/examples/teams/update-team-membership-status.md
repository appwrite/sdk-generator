/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

teams: Teams =  Teams(client);

result = teams.updateTeamMembershipStatus("[TEAM_ID]", "[INVITE_ID]", "[USER_ID]", "[SECRET]");
