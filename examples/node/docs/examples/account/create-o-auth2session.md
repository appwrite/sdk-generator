const sdk = require('node-appwrite');

// Init SDK
let client = new sdk.Client();

let account = new sdk.Account(client);

client
    .setProject('5df5acd0d48c2') // Your project ID
;

let promise = account.createOAuth2Session('bitbucket', 'https://example.com', 'https://example.com');

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});