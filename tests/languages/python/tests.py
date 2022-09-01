from appwrite.client import Client
from appwrite.services.foo import Foo
from appwrite.services.bar import Bar
from appwrite.services.general import General
from appwrite.exception import AppwriteException
from appwrite.input_file import InputFile
from appwrite.query import Query
from appwrite.permission import Permission
from appwrite.role import Role
from appwrite.id import ID

import os.path


client = Client()
foo = Foo(client)
bar = Bar(client)
general = General(client)

client.add_header('Origin', 'http://localhost')
client.set_self_signed()

print("\nTest Started")

# Foo Tests

response = foo.get('string', 123, ['string in array'])
print(response['result'])

response = foo.post('string', 123, ['string in array'])
print(response['result'])

response = foo.put('string', 123, ['string in array'])
print(response['result'])

response = foo.patch('string', 123, ['string in array'])
print(response['result'])

response = foo.delete('string', 123, ['string in array'])
print(response['result'])

# Bar Tests

response = bar.get('string',123, ['string in array'])
print(response['result'])

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

response = general.upload('string', 123, ['string in array'], InputFile.from_path('./tests/resources/file.png'))
print(response['result'])

response = general.upload('string', 123, ['string in array'], InputFile.from_path('./tests/resources/large_file.mp4'))
print(response['result'])

data = open('./tests/resources/file.png', 'rb').read()
response = general.upload('string', 123, ['string in array'], InputFile.from_bytes(data, 'file.png', 'image/png'))
print(response['result'])

data = open('./tests/resources/large_file.mp4', 'rb').read()
response = general.upload('string', 123, ['string in array'], InputFile.from_bytes(data, 'large_file.mp4','video/mp4'))
print(response['result'])

try:
    response = general.error400()
except AppwriteException as e:
    print(e.message)

try:
    response = general.error500()
except AppwriteException as e:
    print(e.message)

try:
    response = general.error502()
except AppwriteException as e:
    print(e.message)

general.empty()

# Query helper tests
print(Query.equal('title', ['Spiderman', 'Dr. Strange']))
print(Query.notEqual('title', 'Spiderman'))
print(Query.lessThan('releasedYear', 1990))
print(Query.greaterThan('releasedYear', 1990))
print(Query.search('name', 'john'))
print(Query.orderAsc("title"))
print(Query.orderDesc("title"))
print(Query.cursorAfter("my_movie_id"))
print(Query.cursorBefore("my_movie_id"))
print(Query.limit(50))
print(Query.offset(20))

# Permission & Role helper tests
print(Permission.read(Role.any()))
print(Permission.write(Role.user(ID.custom('userid'))))
print(Permission.create(Role.users()))
print(Permission.update(Role.guests()))
print(Permission.delete(Role.team('teamId', 'owner')))
print(Permission.delete(Role.team('teamId')))

# ID helper tests
print(ID.unique())
print(ID.custom('custom_id'))

response = general.headers()
print(response['result'])