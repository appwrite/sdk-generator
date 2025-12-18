const {
    Client,
    Permission,
    Query,
    Role,
    ID,
    Operator,
    Condition,
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

    // Foo (Object params)
    response = await foo.get({
        x: 'string',
        y: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await foo.post({
        x: 'string',
        y: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await foo.put({
        x: 'string',
        y: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await foo.patch({
        x: 'string',
        y: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await foo.delete({
        x: 'string',
        y: 123,
        z: ['string in array']
    });
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

    // Bar (Object params)
    response = await bar.get({
        required: 'string',
        xdefault: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await bar.post({
        required: 'string',
        xdefault: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await bar.put({
        required: 'string',
        xdefault: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await bar.patch({
        required: 'string',
        xdefault: 123,
        z: ['string in array']
    });
    console.log(response.result);

    response = await bar.delete({
        required: 'string',
        xdefault: 123,
        z: ['string in array']
    });
    console.log(response.result);

    // General
    response = await general.redirect();
    console.log(response.result);

    // Upload
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

    // Upload (Object params)
    response = await general.upload({
        x: 'string',
        y: 123,
        z: ['string in array'],
        file: InputFile.fromPath(__dirname + '/../../resources/file.png', 'file.png')
    });
    console.log(response.result);

    response = await general.upload({
        x: 'string',
        y: 123,
        z: ['string in array'],
        file: InputFile.fromPath(__dirname + '/../../resources/large_file.mp4', 'large_file.mp4')
    });
    console.log(response.result);

    response = await general.upload({
        x: 'string',
        y: 123,
        z: ['string in array'],
        file: InputFile.fromBuffer(smallBuffer, 'file.png'),
        onProgress: (progress) => {
            // nothing
        }
    });
    console.log(response.result);

    response = await general.upload({
        x: 'string',
        y: 123,
        z: ['string in array'],
        file: InputFile.fromBuffer(largeBuffer, 'large_file.mp4'),
        onProgress: (progress) => {
            // nothing
        }
    });
    console.log(response.result);

    // Enum
    response = await general.enum(MockType.First);
    console.log(response.result);

    try {
        response = await general.error400();
    } catch(error) {
        console.log(error.message);
        console.log(error.response);
    }
    
    try {
        response = await general.error500();
    } catch(error) {
        console.log(error.message);
        console.log(error.response);
    }
    
    try {
        response = await general.error502();
    } catch(error) {
        console.log(error.message);
        console.log(error.response);
    }

    try {
        client.setEndpoint("htp://cloud.appwrite.io/v1");
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
    console.log(Query.orderRandom());
    console.log(Query.cursorAfter("my_movie_id"));
    console.log(Query.cursorBefore("my_movie_id"));
    console.log(Query.limit(50));
    console.log(Query.offset(20));
    console.log(Query.contains("title", "Spider"));
    console.log(Query.contains("labels", "first"));
    
    // New query methods
    console.log(Query.notContains("title", "Spider"));
    console.log(Query.notSearch("name", "john"));
    console.log(Query.notBetween("age", 50, 100));
    console.log(Query.notStartsWith("name", "Ann"));
    console.log(Query.notEndsWith("name", "nne"));
    console.log(Query.createdBefore("2023-01-01"));
    console.log(Query.createdAfter("2023-01-01"));
    console.log(Query.createdBetween("2023-01-01", "2023-12-31"));
    console.log(Query.updatedBefore("2023-01-01"));
    console.log(Query.updatedAfter("2023-01-01"));
    console.log(Query.updatedBetween("2023-01-01", "2023-12-31"));

    // Spatial Distance query tests
    console.log(Query.distanceEqual("location", [[40.7128, -74], [40.7128, -74]], 1000));
    console.log(Query.distanceEqual("location", [40.7128, -74], 1000, true));
    console.log(Query.distanceNotEqual("location", [40.7128, -74], 1000));
    console.log(Query.distanceNotEqual("location", [40.7128, -74], 1000, true));
    console.log(Query.distanceGreaterThan("location", [40.7128, -74], 1000));
    console.log(Query.distanceGreaterThan("location", [40.7128, -74], 1000, true));
    console.log(Query.distanceLessThan("location", [40.7128, -74], 1000));
    console.log(Query.distanceLessThan("location", [40.7128, -74], 1000, true));

    // Spatial query tests
    console.log(Query.intersects("location", [40.7128, -74]));
    console.log(Query.notIntersects("location", [40.7128, -74]));
    console.log(Query.crosses("location", [40.7128, -74]));
    console.log(Query.notCrosses("location", [40.7128, -74]));
    console.log(Query.overlaps("location", [40.7128, -74]));
    console.log(Query.notOverlaps("location", [40.7128, -74]));
    console.log(Query.touches("location", [40.7128, -74]));
    console.log(Query.notTouches("location", [40.7128, -74]));
    console.log(Query.contains("location", [[40.7128, -74], [40.7128, -74]]));
    console.log(Query.notContains("location", [[40.7128, -74], [40.7128, -74]]));
    console.log(Query.equal("location", [[40.7128, -74], [40.7128, -74]]));
    console.log(Query.notEqual("location", [[40.7128, -74], [40.7128, -74]]));
    
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

    // Channel helper tests
    console.log(Channel.database());
    console.log(Channel.database('db1', 'col1', 'doc1'));
    console.log(Channel.database('db1', 'col1', 'doc1', 'create'));
    console.log(Channel.tablesdb());
    console.log(Channel.tablesdb('db1', 'table1', 'row1'));
    console.log(Channel.tablesdb('db1', 'table1', 'row1', 'update'));
    console.log(Channel.account());
    console.log(Channel.account('user123'));
    console.log(Channel.files());
    console.log(Channel.files('bucket1', 'file1'));
    console.log(Channel.files('bucket1', 'file1', 'delete'));
    console.log(Channel.executions());
    console.log(Channel.executions('func1', 'exec1'));
    console.log(Channel.executions('func1', 'exec1', 'create'));
    console.log(Channel.teams());
    console.log(Channel.teams('team1'));
    console.log(Channel.teams('team1', 'create'));
    console.log(Channel.memberships());
    console.log(Channel.memberships('membership1'));
    console.log(Channel.memberships('membership1', 'update'));

    // Operator helper tests
    console.log(Operator.increment(1));
    console.log(Operator.increment(5, 100));
    console.log(Operator.decrement(1));
    console.log(Operator.decrement(3, 0));
    console.log(Operator.multiply(2));
    console.log(Operator.multiply(3, 1000));
    console.log(Operator.divide(2));
    console.log(Operator.divide(4, 1));
    console.log(Operator.modulo(5));
    console.log(Operator.power(2));
    console.log(Operator.power(3, 100));
    console.log(Operator.arrayAppend(["item1", "item2"]));
    console.log(Operator.arrayPrepend(["first", "second"]));
    console.log(Operator.arrayInsert(0, "newItem"));
    console.log(Operator.arrayRemove("oldItem"));
    console.log(Operator.arrayUnique());
    console.log(Operator.arrayIntersect(["a", "b", "c"]));
    console.log(Operator.arrayDiff(["x", "y"]));
    console.log(Operator.arrayFilter(Condition.Equal, "test"));
    console.log(Operator.stringConcat("suffix"));
    console.log(Operator.stringReplace("old", "new"));
    console.log(Operator.toggle());
    console.log(Operator.dateAddDays(7));
    console.log(Operator.dateSubDays(3));
    console.log(Operator.dateSetNow());

    response = await general.headers();
    console.log(response.result);
}

start().catch((err) => {
    console.log(err);
});