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
from appwrite.models.player import Player

import json
import os.path
import requests_mock

client = Client()
foo = Foo(client)
bar = Bar(client)
general = General(client)

client.add_header('Origin', 'http://localhost')
client.set_project('console')
client.set_self_signed()

print("\nTest Started")
sdk_headers = client.get_headers()
print(f"x-sdk-name: {sdk_headers['x-sdk-name']}; x-sdk-platform: {sdk_headers['x-sdk-platform']}; x-sdk-language: {sdk_headers['x-sdk-language']}; x-sdk-version: {sdk_headers['x-sdk-version']}")

# Foo Tests

response = foo.get('string', 123, ['string in array'])
print(response.result)

response = foo.post('string', 123, ['string in array'])
print(response.result)

response = foo.put('string', 123, ['string in array'])
print(response.result)

response = foo.patch('string', 123, ['string in array'])
print(response.result)

response = foo.delete('string', 123, ['string in array'])
print(response.result)

# Bar Tests

response = bar.get('string',123, ['string in array'])
print(response.result)

response = bar.post('string', 123, ['string in array'])
print(response.result)

response = bar.put('string', 123, ['string in array'])
print(response.result)

response = bar.patch('string', 123, ['string in array'])
print(response.result)

response = bar.delete('string', 123, ['string in array'])
print(response.result)

# General Tests

response = general.redirect()
print(response['result'])

# String-list validation follows the declared item type across query and body
# parameters, before reaching the request boundary.
with requests_mock.Mocker() as http:
    for values in ['query', {'method': 'limit'}, [1], [True], [None], [['nested']], ['valid', {'method': 'limit'}]]:
        calls = [
            (general.list_rows, {'queries': values}, 'queries'),
            (foo.get, {'x': 'string', 'y': 123, 'z': values}, 'z'),
            (foo.post, {'x': 'string', 'y': 123, 'z': values}, 'z'),
        ]
        if values != [None]:
            calls.append((general.create_documents, {'documents': [{'$id': 'one'}], 'labels': values}, 'labels'))
        for method, arguments, name in calls:
            try:
                method(**arguments)
                raise AssertionError('Invalid string list was accepted')
            except AppwriteException as error:
                if error.type != 'sdk_input_validation' or error.code != 0 or error.response is not None:
                    raise
                if name not in error.message:
                    raise AssertionError('Validation error did not identify the parameter')

    for arguments, name in [({'x': None, 'y': 123, 'z': [1]}, 'x'), ({'x': 'string', 'y': 123, 'z': None}, 'z')]:
        try:
            foo.get(**arguments)
            raise AssertionError('Missing required parameter was accepted')
        except AppwriteException as error:
            if error.message != f'Missing required parameter: "{name}"':
                raise
    if http.called:
        raise AssertionError('Invalid input submitted an HTTP request')
print('String list validation:passed')

# String contents are unchanged; this validates types, not JSON syntax or enum
# membership. Optional lists and normalized Enum values remain supported.
queries = [Query.equal('name', 'Zoë'), Query.limit(1)]
for values, expected in [
    (None, []),
    ([], []),
    (queries, queries),
    (['not JSON', MockType.FIRST], ['not JSON', 'first']),
]:
    if json.loads(general.list_rows(values).result) != expected:
        raise AssertionError('Query parameter values changed during serialization')

# The generic request serializer also handles scalar array values independently
# of generated service parameter validation.
response = client.call('get', '/mock/tests/general/list-rows', params={'queries': [0, 1.5, True, False]})
if json.loads(response['result']) != ['0', '1.5', 'true', 'false']:
    raise AssertionError('Scalar array values changed during serialization')
print('Query parameter serialization:passed')

# Raw requests still reach API validation for invalid nested queries.
for values in [
    [{'method': 'limit', 'values': [1]}],
    [['nested']],
    [Query.limit(1), {'method': 'limit', 'values': [1]}],
]:
    try:
        client.call('get', '/mock/tests/general/list-rows', params={'queries': values})
        raise AssertionError('Nested query was accepted')
    except AppwriteException as error:
        if error.code != 400 or 'queries' not in error.message:
            raise
print('Nested query validation:400')

# Generated object-array parameters preserve their contents, and nullable
# string-list items follow the schema independently of outer optionality.
documents = [
    {'$id': 'first', 'values': [0, 1.5, True, False]},
    {'$id': 'second', 'nested': [{'name': 'Zoë', 'values': [[1, 2]]}]},
]
for labels, expected in [(None, None), ([], []), (['ready', None], ['ready', None]), ([MockType.FIRST], ['first'])]:
    response = general.create_documents(documents, labels=labels)
    if response.to_dict()['documents'] != documents or response.to_dict()['labels'] != expected:
        raise AssertionError('Object arrays or nullable string-list items changed')

# Multipart serialization preserves the nested structure at the API boundary.
response = client.call(
    'post',
    '/mock/tests/general/documents',
    {'content-type': 'multipart/form-data', 'X-Appwrite-Project': 'console'},
    {
        'documents': documents,
        'file': InputFile.from_bytes(b'fixture', 'fixture.txt', 'text/plain'),
    },
)
if response['documents'] != [
    {'$id': 'first', 'values': ['0', '1.5', 'true', 'false']},
    {'$id': 'second', 'nested': [{'name': 'Zoë', 'values': [['1', '2']]}]},
]:
    raise AssertionError('Multipart document values or nesting changed')
print(response['result'])

for id, plain in [('', '0'), ('0', '')]:
    try:
        general.validate_path(plain, id)
        raise AssertionError('Empty path parameter was accepted')
    except AppwriteException as error:
        print(error.message)
print(general.validate_path('0', '0').result)

response = general.upload('string', 123, ['string in array'], InputFile.from_path('./tests/resources/file.png'))
print(response.result)

response = general.upload('string', 123, ['string in array'], InputFile.from_path('./tests/resources/large_file.mp4'))
print(response.result)

data = open('./tests/resources/file.png', 'rb').read()
response = general.upload('string', 123, ['string in array'], InputFile.from_bytes(data, 'file.png', 'image/png'))
print(response.result)

data = open('./tests/resources/large_file.mp4', 'rb').read()
response = general.upload('string', 123, ['string in array'], InputFile.from_bytes(data, 'large_file.mp4','video/mp4'))
print(response.result)

print(general.download().decode())

response = general.enum(MockType.FIRST)
print(response.result)

# Request model tests
response = general.create_player(Player(id='player1', name='John Doe', score=100))
print(response.result)

response = general.create_players([
    Player(id='player1', name='John Doe', score=100),
    Player(id='player2', name='Jane Doe', score=200),
])
print(response.result)

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
print(Query.contains_any("labels", ["first", "second"]))
print(Query.contains_all("labels", ["first", "second"]))

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
print(Query.vector_dot("embedding", [0.1, 0.2, 0.3]))
print(Query.vector_cosine("embedding", [0.1, 0.2, 0.3]))
print(Query.vector_euclidean("embedding", [0.1, 0.2, 0.3]))

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
print(response.result)
