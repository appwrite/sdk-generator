import * as appwrite from '../../sdks/deno/mod.ts'

// TODO: Correct test typings and remove '// @ts-ignore'

async function start() {
  var response

  // Init SDK
  let client = new appwrite.Client()

  let foo = new appwrite.Foo(client)
  let bar = new appwrite.Bar(client)
  let general = new appwrite.General(client)

  client.setEndpoint('http://localhost/v1')
  client.addHeader('Origin', 'http://localhost')

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

  const fileArray = await Deno.readFile('./tests/resources/file.png')
  const fileBlob = new Blob([fileArray.buffer])
  const file = new File([fileBlob], 'file.png')

  // const file = new File([new Blob([await Deno.readFile('./tests/resources/file.png').buffer])], 'file.png')

  response = await general.upload('string', 123, ['string in array'], file)
  // @ts-ignore
  console.log(response.result)
}

start()
