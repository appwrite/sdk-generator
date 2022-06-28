import * as appwrite from '../../sdks/deno/mod.ts'

// TODO: Correct test typings and remove '// @ts-ignore'

async function start() {
  var response

  // Init SDK
  let client = new appwrite.Client()

  let foo = new appwrite.Foo(client, 'string')
  let bar = new appwrite.Bar(client)
  let general = new appwrite.General(client)

  client.addHeader('Origin', 'http://localhost')

  console.log('\nTest Started');

  // Foo

  response = await foo.get(123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.post(123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.put(123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.patch(123, ['string in array'])
  // @ts-ignore
  console.log(response.result)

  response = await foo.delete(123, ['string in array'])
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
}

start().catch((err) => {
  console.error(err);
});
