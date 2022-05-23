from appwrite.aio.client import Client
from appwrite.aio.services.foo import Foo
from appwrite.aio.services.bar import Bar
from appwrite.aio.services.general import General
from appwrite.aio.exception import AppwriteException
import os.path
import asyncio


loop = asyncio.get_event_loop()

client = Client()
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

async def test_general_upload():
    response = await general.upload('string', 123, ['string in array'], './tests/resources/file.png')
    print(response['result'])

loop.run_until_complete(test_general_upload())

async def test_general_large_file():
    response = await general.upload('string', 123, ['string in array'], './tests/resources/large_file.mp4')
    print(response['result'])

loop.run_until_complete(test_general_large_file())


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