const sdk = require('node-appwrite');

// Init SDK
let client = new Auth.Client();

let auth = new sdk.Auth(client);

client
    .setProject('')
;

let promise = auth.oauthCallback('[PROJECT_ID]', 'bitbucket', '[CODE]');

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});