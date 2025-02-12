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
from appwrite.enums.mock_type import MockType

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

response = general.enum(MockType.FIRST)
print(response['result'])

try:
    response = general.error400()
except AppwriteException as e:
    print(e.message)
    print(e.response)

try:
    response = general.error500()
except AppwriteException as e:
    print(e.message)
    print(e.response)

try:
    response = general.error502()
except AppwriteException as e:
    print(e.message)
    print(e.response)

general.empty()

url = general.oauth2(
    'clientId',
    ['test'],
    '123456',
    'https://localhost',
    'https://localhost'
)
print(url)

# Query helper tests
print(Query.equal("released", [True]))
print(Query.equal("title", ["Spiderman", "Dr. Strange"]))
print(Query.not_equal("title", "Spiderman"))
print(Query.less_than("releasedYear", 1990))
print(Query.greater_than("releasedYear", 1990))
print(Query.search("name", "john"))
print(Query.is_null("name"))
print(Query.is_not_null("name"))
print(Query.between("age", 50, 100))
print(Query.between("age", 50.5, 100.5))
print(Query.between("name", "Anna", "Brad"))
print(Query.starts_with("name", "Ann"))
print(Query.ends_with("name", "nne"))
print(Query.select(["name", "age"]))
print(Query.order_asc("title"))
print(Query.order_desc("title"))
print(Query.cursor_after("my_movie_id"))
print(Query.cursor_before("my_movie_id"))
print(Query.limit(50))
print(Query.offset(20))
print(Query.contains("title", "Spider"))
print(Query.contains("labels", "first"))
print(Query.or_queries(
    [Query.equal("released", True), Query.less_than("releasedYear", 1990)]
))
print(Query.and_queries(
    [Query.equal("released", False), Query.greater_than("releasedYear", 2015)]
))

# Permission & Role helper tests
print(Permission.read(Role.any()))
print(Permission.write(Role.user(ID.custom('userid'))))
print(Permission.create(Role.users()))
print(Permission.update(Role.guests()))
print(Permission.delete(Role.team('teamId', 'owner')))
print(Permission.delete(Role.team('teamId')))
print(Permission.create(Role.member('memberId')))
print(Permission.update(Role.users('verified')))
print(Permission.update(Role.user(ID.custom('userid'), 'unverified')))
print(Permission.create(Role.label('admin')))

# ID helper tests
print(ID.unique())
print(ID.custom('custom_id'))

response = general.headers()
print(response['result'])