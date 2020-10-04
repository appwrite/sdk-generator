const { stderr, stdout } = Deno
import * as sdk from "https://deno.land/x/appwrite/mod.ts";

stderr.writeSync(new TextEncoder().encode('a\n'));
stdout.writeSync(new TextEncoder().encode('b\n'));

Deno.exit(1); // the exit code is optional and defaults to 0

// Init SDK
let client = new sdk.Client();

let users = new sdk.Users(client);

client
    .setEndpoint('http://localhost/v1')
    .setProject('5e63e0a61d9c2') // Your project ID
    .setKey('b3f5137087886e76f60c4a7a4f6a346bfb2af2212ead5b3327719802da106b909a0244f419016d3f03d6b80922764a5783c2a74f4912d2a4a281ca7f6166fbce7544ddb4524af6334ee757e0ec2e928971d875a5dd68b948ef347e1c8a6dc27b82f6d98cd5c4f9e79abc45c6c829edd3e254bc2d708b12797ed71a05fb6facbb') // Your secret API key
;

let promise = users.create('emailxx@example.com', 'password');

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});
