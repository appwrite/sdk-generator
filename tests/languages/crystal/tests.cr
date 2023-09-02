require "../../sdks/crystal/src/appwrite/**"

include Appwrite

client = Client.new
client.add_header("Origin", "http://localhost")

foo = Foo.new(client)
bar = Bar.new(client)
general = General.new(client)

puts ""
puts "Test Started"

# Foo
response = foo.get(x: "string", y: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = foo.post(x: "string", y: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = foo.put(x: "string", y: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = foo.patch(x: "string", y: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = foo.delete(x: "string", y: 123, z: ["string in array"])
puts response.as(Models::Mock).result

# Bar

response = bar.get(required: "string", default: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = bar.post(required: "string", default: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = bar.put(required: "string", default: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = bar.patch(required: "string", default: 123, z: ["string in array"])
puts response.as(Models::Mock).result

response = bar.delete(required: "string", default: 123, z: ["string in array"])
puts response.as(Models::Mock).result

# General

response = general.redirect()
puts response["result"]

response = general.upload(x: "string", y: 123, z:["string in array"], file: InputFile.from_path("./tests/resources/file.png"))
puts response.as(Models::Mock).result

response = general.upload(x: "string", y: 123, z:["string in array"], file: InputFile.from_path("./tests/resources/large_file.mp4"))
puts response.as(Models::Mock).result

string = File.read("./tests/resources/file.png")
response = general.upload(x: "string", y: 123, z:["string in array"], file: InputFile.from_string(string, filename:"file.png", mime_type: "image/png"))
puts response.as(Models::Mock).result

string = File.read("./tests/resources/large_file.mp4")
response = general.upload(x: "string", y: 123, z:["string in array"], file: InputFile.from_string(string, filename:"large_file.mp4", mime_type: "video/mp4"))
puts response.as(Models::Mock).result

begin
    general.error400()
rescue e
    puts e.message
end

begin
    general.error500()
rescue e
    puts e.message
end

begin
    general.error502()
rescue e
    puts e.message
end

general.empty()

# Query helper tests
puts Query.equal("released", [true])
puts Query.equal("title", ["Spiderman", "Dr. Strange"])
puts Query.not_equal("title", "Spiderman")
puts Query.less_than("releasedYear", 1990)
puts Query.greater_than("releasedYear", 1990)
puts Query.search("name", "john")
puts Query.is_null("name")
puts Query.is_not_null("name")
puts Query.between("age", 50, 100)
puts Query.between("age", 50.5, 100.5)
puts Query.between("name", "Anna", "Brad")
puts Query.starts_with("name", "Ann")
puts Query.ends_with("name", "nne")
puts Query.select(["name", "age"])
puts Query.order_asc("title")
puts Query.order_desc("title")
puts Query.cursor_after("my_movie_id")
puts Query.cursor_before("my_movie_id")
puts Query.limit(50)
puts Query.offset(20)

# Permission & Role helper tests
puts Permission.read(Role.any())
puts Permission.write(Role.user(ID.custom("userid")))
puts Permission.create(Role.users())
puts Permission.update(Role.guests())
puts Permission.delete(Role.team("teamId", "owner"))
puts Permission.delete(Role.team("teamId"))
puts Permission.create(Role.member("memberId"))
puts Permission.update(Role.users("verified"))
puts Permission.update(Role.user(ID.custom("userid"), "unverified"))
puts Permission.create(Role.label("admin"))

# ID helper tests
puts ID.unique()
puts ID.custom("custom_id")

response = general.headers()
puts response.as(Models::Mock).result