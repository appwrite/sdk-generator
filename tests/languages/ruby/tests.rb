require_relative '../../sdks/ruby/lib/appwrite'

client = Appwrite::Client.new()
client.add_header('Origin', 'http://localhost')

foo = Appwrite::Foo.new(client)  
bar = Appwrite::Bar.new(client)  
general = Appwrite::General.new(client)  

puts ''
puts 'Test Started'

# Foo

response = foo.get(x: 'string', y: 123, z: ['string in array']);
puts response['result']

response = foo.post(x: 'string', y: 123, z: ['string in array']);
puts response['result']

response = foo.put(x: 'string', y: 123, z: ['string in array']);
puts response['result']

response = foo.patch(x: 'string', y: 123, z: ['string in array']);
puts response['result']

response = foo.delete(x: 'string', y: 123, z: ['string in array']);
puts response['result']

# Bar

response = bar.get(required: 'string', default: 123, z: ['string in array']);
puts response['result']

response = bar.post(required: 'string', default: 123, z: ['string in array']);
puts response['result']

response = bar.put(required: 'string', default: 123, z: ['string in array']);
puts response['result']

response = bar.patch(required: 'string', default: 123, z: ['string in array']);
puts response['result']

response = bar.delete(required: 'string', default: 123, z: ['string in array']);
puts response['result']

# General

response = general.redirect();
puts response['result']


file = Appwrite::File.new('./tests/resources/file.png')
response = general.upload(x:'string',y: 123,z:['string in array'], file: file);
puts response['result']

begin
    response = general.error400()
rescue Appwrite::Exception => error
    puts error.message
end

begin
    response = general.error500()
rescue Appwrite::Exception => error
    puts error.message
end

begin
    response = general.error502()
rescue Appwrite::Exception => error
    puts error.message
end