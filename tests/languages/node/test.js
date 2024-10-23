const { 
    Client, 
    Permission,
    Query,
    Role,
    ID,
    MockType,
    Foo,
    Bar,
    General
} = require('./dist/index.js');
const { InputFile } = require('./dist/inputFile.js');
const { readFile } = require('fs/promises');

async function start() {
    let response;

    // Init SDK
    const client = new Client()
        .addHeader("Origin", "http://localhost")
        .setProject('123456')
        .setSelfSigned(true);

    const foo = new Foo(client);
    const bar = new Bar(client);
    const general = new General(client);

    client.addHeader('Origin', 'http://localhost');

    console.log('\nTest Started');

    // Ping

    response = await client.ping();
    console.log(response.result);

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

    response = await general.upload('string', 123, ['string in array'], InputFile.fromPath(__dirname + '/../../resources/file.png', 'file.png'));
    console.log(response.result);

    response = await general.upload('string', 123, ['string in array'], InputFile.fromPath(__dirname + '/../../resources/large_file.mp4', 'large_file.mp4'));
    console.log(response.result);

    const smallBuffer = await readFile('./tests/resources/file.png');
    response = await general.upload('string', 123, ['string in array'], InputFile.fromBuffer(smallBuffer, 'file.png'))
    console.log(response.result);

    const largeBuffer = await readFile('./tests/resources/large_file.mp4');
    response = await general.upload('string', 123, ['string in array'], InputFile.fromBuffer(largeBuffer, 'large_file.mp4'))
    console.log(response.result);

    response = await general.enum(MockType.First);
    console.log(response.result);

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
    } catch(error) {
        console.log(error.message);
    }

    await general.empty();

    const url = await general.oauth2(
        'clientId',
        ['test'],
        '123456',
        'https://localhost',
        'https://localhost'
    )
    console.log(url)

    // Query helper tests
    console.log(Query.equal("released", [true]));
    console.log(Query.equal("title", ["Spiderman", "Dr. Strange"]));
    console.log(Query.notEqual("title", "Spiderman"));
    console.log(Query.lessThan("releasedYear", 1990));
    console.log(Query.greaterThan("releasedYear", 1990));
    console.log(Query.search("name", "john"));
    console.log(Query.isNull("name"));
    console.log(Query.isNotNull("name"));
    console.log(Query.between("age", 50, 100));
    console.log(Query.between("age", 50.5, 100.5));
    console.log(Query.between("name", "Anna", "Brad"));
    console.log(Query.startsWith("name", "Ann"));
    console.log(Query.endsWith("name", "nne"));
    console.log(Query.select(["name", "age"]));
    console.log(Query.orderAsc("title"));
    console.log(Query.orderDesc("title"));
    console.log(Query.cursorAfter("my_movie_id"));
    console.log(Query.cursorBefore("my_movie_id"));
    console.log(Query.limit(50));
    console.log(Query.offset(20));
    console.log(Query.contains("title", "Spider"));
    console.log(Query.contains("labels", "first"));
    console.log(Query.or([
      Query.equal("released", true),
      Query.lessThan("releasedYear", 1990)
    ]));
    console.log(Query.and([
        Query.equal("released", false),
        Query.greaterThan("releasedYear", 2015)
    ]));

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

start().catch((err) => {
    console.log(err);
});