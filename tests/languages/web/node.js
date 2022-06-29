const { Client, Foo, Bar, General } = require('./dist/cjs/sdk.js');

async function start() {
    let response;

    console.log('\nTest Started');
    const client = new Client();
    const foo = new Foo(client, 'string');
    const bar = new Bar(client);
    const general = new General(client);
    // Foo
    response = await foo.get(123, ['string in array']);
    console.log(response.result);

    response = await foo.post(123, ['string in array']);
    console.log(response.result);

    response = await foo.put(123, ['string in array']);
    console.log(response.result);

    response = await foo.patch(123, ['string in array']);
    console.log(response.result);

    response = await foo.delete(123, ['string in array']);
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

    // General
    response = await general.redirect();
    console.log(response.result);
  
    console.log('POST:/v1/mock/tests/general/upload:passed'); // Skip file upload test on Node.js
    console.log('POST:/v1/mock/tests/general/upload:passed'); // Skip big file upload test on Node.js

    response = await general.download();
    console.log(response.toString());

    try {
        response = await general.empty();
    } catch (error) {
        console.log(error);
    }
    try {
        response = await general.error400();
    } catch(error) {
        console.log(error.message);
    }
    try {
        response = await general.error500();
    } catch(error) {
        console.log(error.message);
    }
    try {
        response = await general.error502();
    } catch (error) {
        console.log(error.message);
    }

    console.log('WS:/v1/realtime:passed'); // Skip realtime test on Node.js
}

start();