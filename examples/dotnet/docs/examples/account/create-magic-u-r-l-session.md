using Appwrite;

Client client = new Client();

client
  .SetEndPoint("https://[HOSTNAME_OR_IP]/v1") // Your API Endpoint
  .SetProject("5df5acd0d48c2") // Your project ID
;

Account account = new Account(client);

HttpResponseMessage result = await account.CreateMagicURLSession("email@example.com");
