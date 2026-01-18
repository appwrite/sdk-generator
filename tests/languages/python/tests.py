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
from appwrite.operator import Operator, Condition
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

# Request model tests
response = general.create_player({'id': 'player1', 'name': 'John Doe', 'score': 100})
print(response['result'])

response = general.create_players([
    {'id': 'player1', 'name': 'John Doe', 'score': 100},
    {'id': 'player2', 'name': 'Jane Doe', 'score': 200}
])
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

try:
    client.set_endpoint("htp://cloud.appwrite.io/v1")
except AppwriteException as e:
    print(e.message)

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
print(Query.order_random())
print(Query.cursor_after("my_movie_id"))
print(Query.cursor_before("my_movie_id"))
print(Query.limit(50))
print(Query.offset(20))
print(Query.contains("title", "Spider"))
print(Query.contains("labels", "first"))

# New query methods
print(Query.not_contains("title", "Spider"))
print(Query.not_search("name", "john"))
print(Query.not_between("age", 50, 100))
print(Query.not_starts_with("name", "Ann"))
print(Query.not_ends_with("name", "nne"))
print(Query.created_before("2023-01-01"))
print(Query.created_after("2023-01-01"))
print(Query.created_between('2023-01-01', '2023-12-31'))
print(Query.updated_before("2023-01-01"))
print(Query.updated_after("2023-01-01"))
print(Query.updated_between('2023-01-01', '2023-12-31'))

# Spatial Distance query tests
print(Query.distance_equal("location", [[40.7128, -74], [40.7128, -74]], 1000))
print(Query.distance_equal("location", [40.7128, -74], 1000, True))
print(Query.distance_not_equal("location", [40.7128, -74], 1000))
print(Query.distance_not_equal("location", [40.7128, -74], 1000, True))
print(Query.distance_greater_than("location", [40.7128, -74], 1000))
print(Query.distance_greater_than("location", [40.7128, -74], 1000, True))
print(Query.distance_less_than("location", [40.7128, -74], 1000))
print(Query.distance_less_than("location", [40.7128, -74], 1000, True))

# Spatial query tests
print(Query.intersects("location", [40.7128, -74]))
print(Query.not_intersects("location", [40.7128, -74]))
print(Query.crosses("location", [40.7128, -74]))
print(Query.not_crosses("location", [40.7128, -74]))
print(Query.overlaps("location", [40.7128, -74]))
print(Query.not_overlaps("location", [40.7128, -74]))
print(Query.touches("location", [40.7128, -74]))
print(Query.not_touches("location", [40.7128, -74]))
print(Query.contains("location", [[40.7128, -74], [40.7128, -74]]))
print(Query.not_contains("location", [[40.7128, -74], [40.7128, -74]]))
print(Query.equal("location", [[40.7128, -74], [40.7128, -74]]))
print(Query.not_equal("location", [[40.7128, -74], [40.7128, -74]]))

print(Query.or_queries(
    [Query.equal("released", True), Query.less_than("releasedYear", 1990)]
))
print(Query.and_queries(
    [Query.equal("released", False), Query.greater_than("releasedYear", 2015)]
))

# New query methods: regex, exists, notExists, elemMatch
print(Query.regex("name", "pattern.*"))
print(Query.exists(["attr1", "attr2"]))
print(Query.not_exists(["attr1", "attr2"]))
print(Query.elem_match("friends", [
    Query.equal("name", "Alice"),
    Query.greater_than("age", 18)
]))

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

# Operator helper tests
print(Operator.increment())
print(Operator.increment(5, 100))
print(Operator.decrement())
print(Operator.decrement(3, 0))
print(Operator.multiply(2))
print(Operator.multiply(3, 1000))
print(Operator.divide(2))
print(Operator.divide(4, 1))
print(Operator.modulo(5))
print(Operator.power(2))
print(Operator.power(3, 100))
print(Operator.array_append(['item1', 'item2']))
print(Operator.array_prepend(['first', 'second']))
print(Operator.array_insert(0, 'newItem'))
print(Operator.array_remove('oldItem'))
print(Operator.array_unique())
print(Operator.array_intersect(['a', 'b', 'c']))
print(Operator.array_diff(['x', 'y']))
print(Operator.array_filter(Condition.EQUAL, 'test'))
print(Operator.string_concat('suffix'))
print(Operator.string_replace('old', 'new'))
print(Operator.toggle())
print(Operator.date_add_days(7))
print(Operator.date_sub_days(3))
print(Operator.date_set_now())

response = general.headers()
print(response['result'])