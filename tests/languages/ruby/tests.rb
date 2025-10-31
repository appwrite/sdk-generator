require_relative '../../sdks/ruby/lib/appwrite'

include Appwrite
include Appwrite::Enums

client = Client.new
client.set_self_signed(true)
client.add_header('Origin', 'http://localhost')

foo = Foo.new(client)
bar = Bar.new(client)
general = General.new(client)

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
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: InputFile.from_path('./tests/resources/file.png'))
    puts response.result
rescue => e
    puts e
end

begin
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: InputFile.from_path('./tests/resources/large_file.mp4'))
    puts response.result
rescue => e
    puts e
end

begin
    string = IO.read('./tests/resources/file.png')
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: InputFile.from_string(string, filename:'file.png', mime_type: 'image/png'))
    puts response.result
rescue => e
    puts e
end

begin
    string = IO.read('./tests/resources/large_file.mp4')
    response = general.upload(x: 'string', y: 123, z:['string in array'], file: InputFile.from_string(string, filename:'large_file.mp4', mime_type: 'video/mp4'))
    puts response.result
rescue => e
    puts e
end

response = general.enum(mock_type: MockType::FIRST)
puts response.result

begin
    general.error400()
rescue Exception => error
    puts error.message
    puts error.response
end

begin
    general.error500()
rescue Exception => error
    puts error.message
    puts error.response
end

begin
    general.error502()
rescue Exception => error
    puts error.message
    puts error.response
end

begin
    client.set_endpoint("htp://cloud.appwrite.io/v1")
rescue Exception => error
    puts error.message
end

general.empty()

url = general.oauth2(
    client_id: 'clientId',
    scopes: ['test'],
    state: '123456',
    success: 'https://localhost',
    failure: 'https://localhost'
)
puts url

# Query helper tests
puts Query.equal('released', [true])
puts Query.equal('title', ['Spiderman', 'Dr. Strange'])
puts Query.not_equal('title', 'Spiderman')
puts Query.less_than('releasedYear', 1990)
puts Query.greater_than('releasedYear', 1990)
puts Query.search('name', 'john')
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
puts Query.order_random()
puts Query.cursor_after("my_movie_id")
puts Query.cursor_before("my_movie_id")
puts Query.limit(50)
puts Query.offset(20)
puts Query.contains("title", "Spider")
puts Query.contains("labels", "first")

# New query methods
puts Query.not_contains("title", "Spider")
puts Query.not_search("name", "john")
puts Query.not_between("age", 50, 100)
puts Query.not_starts_with("name", "Ann")
puts Query.not_ends_with("name", "nne")
puts Query.created_before("2023-01-01")
puts Query.created_after("2023-01-01")
puts Query.created_between('2023-01-01', '2023-12-31')
puts Query.updated_before("2023-01-01")
puts Query.updated_after("2023-01-01")
puts Query.updated_between('2023-01-01', '2023-12-31')

# Spatial Distance query tests
puts Query.distance_equal("location", [[40.7128, -74], [40.7128, -74]], 1000)
puts Query.distance_equal("location", [40.7128, -74], 1000, true)
puts Query.distance_not_equal("location", [40.7128, -74], 1000)
puts Query.distance_not_equal("location", [40.7128, -74], 1000, true)
puts Query.distance_greater_than("location", [40.7128, -74], 1000)
puts Query.distance_greater_than("location", [40.7128, -74], 1000, true)
puts Query.distance_less_than("location", [40.7128, -74], 1000)
puts Query.distance_less_than("location", [40.7128, -74], 1000, true)

# Spatial query tests
puts Query.intersects("location", [40.7128, -74])
puts Query.not_intersects("location", [40.7128, -74])
puts Query.crosses("location", [40.7128, -74])
puts Query.not_crosses("location", [40.7128, -74])
puts Query.overlaps("location", [40.7128, -74])
puts Query.not_overlaps("location", [40.7128, -74])
puts Query.touches("location", [40.7128, -74])
puts Query.not_touches("location", [40.7128, -74])
puts Query.contains("location", [[40.7128, -74], [40.7128, -74]])
puts Query.not_contains("location", [[40.7128, -74], [40.7128, -74]])
puts Query.equal("location", [[40.7128, -74], [40.7128, -74]])
puts Query.not_equal("location", [[40.7128, -74], [40.7128, -74]])

puts Query.or([Query.equal("released", true), Query.less_than("releasedYear", 1990)])
puts Query.and([Query.equal("released", false), Query.greater_than("releasedYear", 2015)])

# Permission & Role helper tests
puts Permission.read(Role.any())
puts Permission.write(Role.user(ID.custom('userid')))
puts Permission.create(Role.users())
puts Permission.update(Role.guests())
puts Permission.delete(Role.team('teamId', 'owner'))
puts Permission.delete(Role.team('teamId'))
puts Permission.create(Role.member('memberId'))
puts Permission.update(Role.users('verified'))
puts Permission.update(Role.user(ID.custom('userid'), 'unverified'))
puts Permission.create(Role.label('admin'))

# ID helper tests
puts ID.unique()
puts ID.custom('custom_id')

# Operator helper tests
puts Operator.increment(1)
puts Operator.increment(5, 100)
puts Operator.decrement(1)
puts Operator.decrement(3, 0)
puts Operator.multiply(2)
puts Operator.multiply(3, 1000)
puts Operator.divide(2)
puts Operator.divide(4, 1)
puts Operator.modulo(5)
puts Operator.power(2)
puts Operator.power(3, 100)
puts Operator.array_append(["item1", "item2"])
puts Operator.array_prepend(["first", "second"])
puts Operator.array_insert(0, "newItem")
puts Operator.array_remove("oldItem")
puts Operator.array_unique()
puts Operator.array_intersect(["a", "b", "c"])
puts Operator.array_diff(["x", "y"])
puts Operator.array_filter(Condition::EQUAL, "test")
puts Operator.string_concat("suffix")
puts Operator.string_replace("old", "new")
puts Operator.toggle()
puts Operator.date_add_days(7)
puts Operator.date_sub_days(3)
puts Operator.date_set_now()

response = general.headers()
puts response.result