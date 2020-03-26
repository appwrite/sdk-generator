const sdk = require('node-appwrite');
const fs = require('fs');

// Init SDK
let client = new sdk.Client();

let storage = new sdk.Storage(client);

client
    .setProject('5df5acd0d48c2') // Your project ID
;

let promise = storage.createFile(fs.createReadStream(__dirname + '/file.png')), [], []);

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});