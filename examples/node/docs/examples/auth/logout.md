const sdk = require('node-appwrite');

// Init SDK
let client = new sdk.Client();

let auth = new sdk.Auth(client);

client
    .setProject('')
;

let promise = auth.logout();

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});