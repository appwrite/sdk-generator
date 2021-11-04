require 'appwrite'

client = Appwrite::Client.new()

client
    .set_endpoint('https://[HOSTNAME_OR_IP]/v1') # Your API Endpoint
    .set_project('5df5acd0d48c2') # Your project ID
;

account = Appwrite::Account.new(client);

response = account.update_magic_url_session(user_id: '[USER_ID]', secret: '[SECRET]');

puts response