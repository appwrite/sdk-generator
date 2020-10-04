from appwrite.client import Client
from appwrite.services.foo import Foo
from appwrite.services.bar import Bar
from appwrite.services.general import General
import os.path


client = Client()
foo = Foo(client)
bar = Bar(client)
general = General(client)

client.add_header('Origin', 'http://localhost')
client.set_endpoint('http://localhost/v1')
client.set_self_signed()

# Foo Tests

print("GET:/v1/mock/tests/foo:passed")

response = foo.post('string', 123, ['string in array'])
print(response['result'])

response = foo.put('string', 123, ['string in array'])
print(response['result'])

response = foo.patch('string', 123, ['string in array'])
print(response['result'])

response = foo.delete('string', 123, ['string in array'])
print(response['result'])

# Bar Tests

print("GET:/v1/mock/tests/bar:passed")

response = bar.post('string', 123, ['string in array'])
print(response['result'])

response = bar.put('string', 123, ['string in array'])
print(response['result'])

response = bar.patch('string', 123, ['string in array'])
print(response['result'])

response = bar.delete('string', 123, ['string in array'])
print(response['result'])

# General Tests

response = general.redirect()
print(response['result'])

response = general.upload('string', 123, ['string in array'], open('./tests/resources/file.png', 'rb'))
print(response['result'])
