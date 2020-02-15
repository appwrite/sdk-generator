const sdk = require('node-appwrite');
const fs = require('fs');

// Init SDK
let client = new sdk.Client();

let storage = new sdk.Storage(client);

client
    .setProject('')
;

let promise = storage.createFile(fs.createReadStream(__dirname + '/file.png')), [], []);

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});