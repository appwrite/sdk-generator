require_relative '../../sdks/ruby/lib/appwrite'

query = Appwrite::Query
permission = Appwrite::Permission
role = Appwrite::Role
id = Appwrite::ID

client = Appwrite::Client.new
client.add_header('Origin', 'http://localhost')

foo = Appwrite::Foo.new(client)  
bar = Appwrite::Bar.new(client)  
general = Appwrite::General.new(client)  

puts ''
puts 'Test Started'

# Foo

response = foo.get(x: 'string', y: 123, z: ['string in array'])
puts response.result

response = foo.post(x: 'string', y: 123, z: ['string in array'])
puts response.result

response = foo.put(x: 'string', y: 123, z: ['string in array'])
puts response.result

response = foo.patch(x: 'string', y: 123, z: ['string in array'])
puts response.result

response = foo.delete(x: 'string', y: 123, z: ['string in array'])
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

begin
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: Appwrite::InputFile.from_path('./tests/resources/file.png'))
    puts response.result
rescue => e
    puts e
end

begin
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: Appwrite::InputFile.from_path('./tests/resources/large_file.mp4'))
    puts response.result
rescue => e
    puts e
end

begin
    string = IO.read('./tests/resources/file.png')
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: Appwrite::InputFile.from_string(string, filename:'file.png', mime_type: 'image/png'))
    puts response.result
rescue => e
    puts e
end

begin
    string = IO.read('./tests/resources/large_file.mp4')
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: Appwrite::InputFile.from_string(string, filename:'large_file.mp4', mime_type: 'video/mp4'))
    puts response.result
rescue => e
    puts e
end

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

# Query helper tests
puts query.equal('title', ['Spiderman', 'Dr. Strange'])
puts query.notEqual('title', 'Spiderman')
puts query.lesser('releasedYear', 1990)
puts query.greater('releasedYear', [1990, 1999])
puts query.search('name', 'john')
puts query.orderAsc("title")
puts query.orderDesc("title")
puts query.cursorAfter("my_movie_id")
puts query.cursorBefore("my_movie_id")
puts query.limit(50)
puts query.offset(20)

# Permission & Role helper tests
puts permission.read(role.any())
puts permission.write(role.user(id.custom('userid')))
puts permission.create(role.users())
puts permission.update(role.guests())
puts permission.delete(role.team('teamId', 'owner'))
puts permission.delete(role.team('teamId'))

# ID helper tests
puts id.unique()
puts id.custom('custom_id')