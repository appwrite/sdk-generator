from appwrite.aio.client import AsyncClient
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
import asyncio


loop = asyncio.get_event_loop()

client = AsyncClient()
foo = Foo(client)
bar = Bar(client)
general = General(client)

client.add_header('Origin', 'http://localhost')
client.set_self_signed()

print("\nTest Started")

# Foo Tests


async def test_foo_get():
    response = await foo.get('string',123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_foo_get())

async def test_foo_post():
    response = await foo.post('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_foo_post())

async def test_foo_put():
    response = await foo.put('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_foo_put())


async def test_foo_patch():
    response = await foo.patch('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_foo_patch())


async def test_foo_delete():
    response = await foo.delete('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_foo_delete())


# Bar Tests

async def test_bar_get():
    response = await bar.get('string',123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_bar_get())

async def test_bar_post():
    response = await bar.post('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_bar_post())


async def test_bar_put():
    response = await bar.put('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_bar_put())

async def test_bar_patch():
    response = await bar.patch('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_bar_patch())

async def test_bar_delete():
    response = await bar.delete('string', 123, ['string in array'])
    print(response['result'])

loop.run_until_complete(test_bar_delete())

# General Tests

async def test_general_redirect():
    response = await general.redirect()
    print(response['result'])

loop.run_until_complete(test_general_redirect())

async def test_general_file_png_upload():
    response = await general.upload('string', 123, ['string in array'], InputFile.from_path('./tests/resources/file.png'))
    print(response['result'])

loop.run_until_complete(test_general_file_png_upload())

async def test_general_file_png_bytes_upload():
    data = open('./tests/resources/file.png', 'rb').read()
    response = await general.upload('string', 123, ['string in array'], InputFile.from_bytes(data, 'file.png', 'image/png'))
    print(response['result'])

loop.run_until_complete(test_general_file_png_bytes_upload())


async def test_general_large_file_mp4_bytes_upload():
    data = open('./tests/resources/large_file.mp4', 'rb').read()
    response = await general.upload('string', 123, ['string in array'], InputFile.from_bytes(data, 'large_file.mp4','video/mp4'))
    print(response['result'])

loop.run_until_complete(test_general_large_file_mp4_bytes_upload())


async def test_general_large_file_mp4():
    response = await general.upload('string', 123, ['string in array'], InputFile.from_path('./tests/resources/large_file.mp4'))
    print(response['result'])

loop.run_until_complete(test_general_large_file_mp4())


async def test_general_400():
    try:
        response = await general.error400()
    except AppwriteException as e:
        print(e.message)

loop.run_until_complete(test_general_400())

async def test_general_error_500():
    try:
        response = await general.error500()
    except AppwriteException as e:
        print(e.message)

loop.run_until_complete(test_general_error_500())

async def test_general_502():
    try:
        response = await general.error502()
    except AppwriteException as e:
        print(e.message)

loop.run_until_complete(test_general_502())

async def test_general_empty():
    await general.empty()

loop.run_until_complete(test_general_empty())



# Query helper tests
print(Query.equal('released', [True]))
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
print(Permission.create(Role.member('memberId')))
print(Permission.update(Role.users('verified')))
print(Permission.update(Role.user(ID.custom('userid'), 'unverified')))

# ID helper tests
print(ID.unique())
print(ID.custom('custom_id'))

response = general.headers()
print(response['result'])
