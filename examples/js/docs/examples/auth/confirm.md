let sdk = new Appwrite();

let promise = sdk.auth.confirm('[USER_ID]', '[TOKEN]');

promise.then(function (response) {
    console.log(response);
}, function (error) {
    console.log(error);
});