/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///


var client: Client = Client()

client
    .setProject(value: "")
    .setEndpoint(endpoint: "http://localhost/v1")

var teams: Teams =  Teams(client: client);

var result = teams.updateTeamMembershipStatus(_teamId: "[TEAM_ID]", _inviteId: "[INVITE_ID]", _userId: "[USER_ID]", _secret: "[SECRET]");
