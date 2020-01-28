/// Swift Appwrite SDK
/// Produced by Appwrite SDK Generator
///

var client: Client = Client()

client
    .setProject('')
;

teams: Teams =  Teams(client);

result = teams.createTeam("[NAME]");
