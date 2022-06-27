require_relative '../../sdks/ruby/lib/appwrite'

client = Appwrite::Client.new
client.add_header('Origin', 'http://localhost')

foo = Appwrite::Foo.new(client, x: 'string')  
bar = Appwrite::Bar.new(client)  
general = Appwrite::General.new(client)  

puts ''
puts 'Test Started'

# Foo

response = foo.get(y: 123, z: ['string in array'])
puts response.result

response = foo.post(y: 123, z: ['string in array'])
puts response.result

response = foo.put(y: 123, z: ['string in array'])
puts response.result

response = foo.patch(y: 123, z: ['string in array'])
puts response.result

response = foo.delete(y: 123, z: ['string in array'])
puts response.result

# Bar

response = bar.get(required: 'string', default: 123, z: ['string in array'])
puts response.result

response = bar.post(required: 'string', default: 123, z: ['string in array'])
puts response.result

response = bar.put(required: 'string', default: 123, z: ['string in array'])
puts response.result

response = bar.patch(required: 'string', default: 123, z: ['string in array'])
puts response.result

response = bar.delete(required: 'string', default: 123, z: ['string in array'])
puts response.result

# General

response = general.redirect()
puts response["result"]

response = general.upload(x: 'string', y: 123, z:['string in array'], file: './tests/resources/file.png')
puts response.result

response = general.upload(x: 'string', y: 123, z:['string in array'], file: './tests/resources/large_file.mp4')
puts response.result

begin
    general.error400()
rescue Appwrite::Exception => error
    puts error.message
end

begin
    general.error500()
rescue Appwrite::Exception => error
    puts error.message
end

begin
    general.error502()
rescue Appwrite::Exception => error
    puts error.message
end

general.empty()