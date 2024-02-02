const { Client, Foo, Bar, General, Query, Permission, Role, ID, MockType } = require('./dist/cjs/sdk.js');

async function start() {
    let response;

    console.log('\nTest Started');
    const client = new Client();
    const foo = new Foo(client);
    const bar = new Bar(client);
    const general = new General(client);
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

    // General
    response = await general.redirect();
    console.log(response.result);
  
    console.log('POST:/v1/mock/tests/general/upload:passed'); // Skip file upload test on Node.js
    console.log('POST:/v1/mock/tests/general/upload:passed'); // Skip big file upload test on Node.js
    console.log('POST:/v1/mock/tests/general/upload:passed'); // Skip file upload test on Node.js
    console.log('POST:/v1/mock/tests/general/upload:passed'); // Skip big file upload test on Node.js

    response = await general.enum(MockType.First);
    console.log(response.result);

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

    // Query helper tests
    console.log(Query.equal("released", [true]).toString());
    console.log(Query.equal("title", ["Spiderman", "Dr. Strange"]).toString());
    console.log(Query.notEqual("title", "Spiderman").toString());
    console.log(Query.lessThan("releasedYear", 1990).toString());
    console.log(Query.greaterThan("releasedYear", 1990).toString());
    console.log(Query.search("name", "john").toString());
    console.log(Query.isNull("name").toString());
    console.log(Query.isNotNull("name").toString());
    console.log(Query.between("age", 50, 100).toString());
    console.log(Query.between("age", 50.5, 100.5).toString());
    console.log(Query.between("name", "Anna", "Brad").toString());
    console.log(Query.startsWith("name", "Ann").toString());
    console.log(Query.endsWith("name", "nne").toString());
    console.log(Query.select(["name", "age"]).toString());
    console.log(Query.orderAsc("title").toString());
    console.log(Query.orderDesc("title").toString());
    console.log(Query.cursorAfter("my_movie_id").toString());
    console.log(Query.cursorBefore("my_movie_id").toString());
    console.log(Query.limit(50).toString());
    console.log(Query.offset(20).toString());
    console.log(Query.contains("title", "Spider").toString());
    console.log(Query.contains("labels", "first").toString());
    console.log(Query.or(
        Query.equal("released", true),
        Query.lessThan("releasedYear", 1990)
    ).toString());
    console.log(Query.and(
        Query.equal("released", false),
        Query.greaterThan("releasedYear", 2015)
    ).toString());

    // Permission & Role helper tests
    console.log(Permission.read(Role.any()));
    console.log(Permission.write(Role.user(ID.custom('userid'))));
    console.log(Permission.create(Role.users()));
    console.log(Permission.update(Role.guests()));
    console.log(Permission.delete(Role.team('teamId', 'owner')));
    console.log(Permission.delete(Role.team('teamId')));
    console.log(Permission.create(Role.member('memberId')));
    console.log(Permission.update(Role.users('verified')));
    console.log(Permission.update(Role.user(ID.custom('userid'), 'unverified')));
    console.log(Permission.create(Role.label('admin')));


    // ID helper tests
    console.log(ID.unique());
    console.log(ID.custom('custom_id'));

    response = await general.headers();
    console.log(response.result);
}

start();