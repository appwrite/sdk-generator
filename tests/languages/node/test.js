
const appwrite = require('../../sdks/node/index');
const InputFile = require('../../sdks/node/lib/inputFile');
const fs = require('fs').promises;

async function start() {
    var response;

    let Permission = appwrite.Permission;
    let Query = appwrite.Query;
    let Role = appwrite.Role;
    let ID = appwrite.ID;
    let MockType = appwrite.MockType;

    // Init SDK
    let client = new appwrite.Client()
        .addHeader("Origin", "http://localhost")
        .setSelfSigned(true);

    let foo = new appwrite.Foo(client);
    let bar = new appwrite.Bar(client);
    let general = new appwrite.General(client);

    client.addHeader('Origin', 'http://localhost');

    console.log('\nTest Started');

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

    let buffer= await fs.readFile('./tests/resources/file.png');
    response = await general.upload('string', 123, ['string in array'], appwrite.InputFile.fromBuffer(buffer, 'file.png'))
    console.log(response.result);

    buffer = await fs.readFile('./tests/resources/large_file.mp4');
    response = await general.upload('string', 123, ['string in array'], appwrite.InputFile.fromBuffer(buffer, 'large_file.mp4'))
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