import * as appwrite from "../../sdks/typescript/index";
//import * as fs from "fs";

async function start() {
    var response;

    // Init SDK
    let client = new appwrite.Client();

    let foo = new appwrite.Foo(client);
    let bar = new appwrite.Bar(client);
    let general = new appwrite.General(client);

    client.addHeader('Origin', 'http://localhost');

    // Foo

    response = await foo.get('string', 123, ['string in array']);
    console.log(response.result);

    response = await foo.post('string', 123, ['string in array']);
    console.log(response.result);

    response = await foo.put('string', 123, ['string in array']);
    console.log(response.result);

    response = await foo.patch('string', 123, ['string in array']);
    console.log(response.result);

    response = await foo.delete('string', 123, ['string in array']);
    console.log(response.result);

    // Bar

    response = await bar.get('string', 123, ['string in array']);
    console.log(response.result);

    response = await bar.post('string', 123, ['string in array']);
    console.log(response.result);

    response = await bar.put('string', 123, ['string in array']);
    console.log(response.result);

    response = await bar.patch('string', 123, ['string in array']);
    console.log(response.result);

    response = await bar.delete('string', 123, ['string in array']);
    console.log(response.result);

    response = await general.redirect();
    console.log(response.result);

    // response = await general.upload('string', 123, ['string in array'], fs.createReadStream(__dirname + '/../../resources/file.png'));
    // console.log(response.result);

}

start();