const sdk = require('node-appwrite');

// Init SDK
let client = new sdk.Client();

let account = new sdk.Account(client);

client
    .setProject('5df5acd0d48c2') // Your project ID
;

let promise = account.deleteSession('[SESSION_ID]');

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});