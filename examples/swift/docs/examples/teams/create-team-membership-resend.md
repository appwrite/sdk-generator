/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

teams: Teams =  Teams(client);

result = teams.createTeamMembershipResend("[TEAM_ID]", "[INVITE_ID]", "https://example.com");
