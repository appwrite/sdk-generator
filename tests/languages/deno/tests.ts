import * as appwrite from '../../sdks/deno/mod.ts'

// TODO: Correct test typings and remove '// @ts-ignore'

async function start() {
  var response

  let Permission = appwrite.Permission
  let Role = appwrite.Role
  let ID = appwrite.ID
  let Query = appwrite.Query

  // Init SDK
  let client = new appwrite.Client()

  let foo = new appwrite.Foo(client)
  let bar = new appwrite.Bar(client)
  let general = new appwrite.General(client)

  client.addHeader('Origin', 'http://localhost')

  console.log('\nTest Started');

  // Foo

  response = await foo.get('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.post('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.put('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.patch('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.delete('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  // Bar

  response = await bar.get('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await bar.post('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await bar.put('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await bar.patch('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await bar.delete('string', 123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await general.redirect()
  // @ts-ignore
  console.log(response.result)

  response = await general.upload('string', 123, ['string in array'], appwrite.InputFile.fromPath('./tests/resources/file.png', 'file.png'))
  // @ts-ignore
  console.log(response.result)

  response = await general.upload('string', 123, ['string in array'], appwrite.InputFile.fromPath('./tests/resources/file.png', 'file.png'))
  // @ts-ignore
  console.log(response.result)

  try {
    response = await general.error400();
  }catch(error){
    console.log(error.message);
  }

  try {
    response = await general.error500();
  }catch(error){
    console.log(error.message);
  }

  try {
    response = await general.error502();
  }catch(error){
    console.log(error.message);
  }

  await general.empty();
  
  // Query helper tests
  console.log(Query.equal('title', ['Spiderman', 'Dr. Strange']));
  console.log(Query.notEqual('title', 'Spiderman'));
  console.log(Query.lesser('releasedYear', 1990));
  console.log(Query.greater('releasedYear', [1990, 1999]));
  console.log(Query.search('name', "john"));
  console.log(Query.orderAsc("title"));
  console.log(Query.orderDesc("title"));
  console.log(Query.cursorAfter("my_movie_id"));
  console.log(Query.cursorBefore("my_movie_id"));
  console.log(Query.limit(50));
  console.log(Query.offset(20));

  // Permission & Role helper tests
  console.log(Permission.read(Role.any()));
  console.log(Permission.write(Role.user(ID.custom('userid'))));
  console.log(Permission.create(Role.users()));
  console.log(Permission.update(Role.guests()));
  console.log(Permission.delete(Role.team('teamId', 'owner')));
  console.log(Permission.delete(Role.team('teamId')));

  // ID helper tests
  console.log(ID.unique());
  console.log(ID.custom('custom_id'));
}

start().catch((err) => {
  console.error(err);
});
