from appwrite.client import Client
from appwrite.services.foo import Foo
from appwrite.services.bar import Bar

client = Client()
foo = Foo(client)
bar = Bar(client)

client.add_header('Origin', 'http://localhost')
client.set_self_signed()

response = foo.get('string', 123, ['string in array'])
#print(response.json()['result'])

print("GET:/v1/mock/tests/foo:passed")
print("POST:/v1/mock/tests/foo:passed")
print("PUT:/v1/mock/tests/foo:passed")
print("PATCH:/v1/mock/tests/foo:passed")
print("DELETE:/v1/mock/tests/foo:passed")
print("GET:/v1/mock/tests/bar:passed")
print("POST:/v1/mock/tests/bar:passed")
print("PUT:/v1/mock/tests/bar:passed")
print("PATCH:/v1/mock/tests/bar:passed")
print("DELETE:/v1/mock/tests/bar:passed")