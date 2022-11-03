IO.puts("Test Started")

print_result = fn
  {:ok, %{status: 204}} -> :ok
  {:ok, %{body: %{"result" => result}}} -> IO.puts(result)
  {:error, {:appwrite_error, message, _, _}} -> IO.puts(message)
end

alias Appwrite.Client
alias Appwrite.Services.Foo
alias Appwrite.Services.Bar
alias Appwrite.Services.General
alias Appwrite.InputFile
alias Appwrite.Query
alias Appwrite.Permission
alias Appwrite.Role
alias Appwrite.Id

Logger.configure(level: :error)

client = Client.get_client
|> Client.add_header("Origin", "http://localhost")
|> Client.set_self_signed(true)

Foo.get(client, "string", 123, ["string in array"]) |> print_result.()
Foo.post(client, "string", 123, ["string in array"]) |> print_result.()
Foo.put(client, "string", 123, ["string in array"]) |> print_result.()
Foo.patch(client, "string", 123, ["string in array"]) |> print_result.()
Foo.delete(client, "string", 123, ["string in array"]) |> print_result.()

Bar.get(client, "string", 123, ["string in array"]) |> print_result.()
Bar.post(client, "string", 123, ["string in array"]) |> print_result.()
Bar.put(client, "string", 123, ["string in array"]) |> print_result.()
Bar.patch(client, "string", 123, ["string in array"]) |> print_result.()
Bar.delete(client, "string", 123, ["string in array"]) |> print_result.()

General.redirect(client) |> print_result.()

General.upload(client, "string", 123, ["string in array"], InputFile.from_path("../../resources/file.png"))
|> print_result.()

General.upload(client, "string", 123, ["string in array"], InputFile.from_path("../../resources/large_file.mp4"))
|> print_result.()

General.upload(client, "string", 123, ["string in array"], File.read!("../../resources/file.png") |> InputFile.from_binary("file.png"))
|> print_result.()

General.upload(client, "string", 123, ["string in array"], File.read!("../../resources/large_file.mp4") |> InputFile.from_binary("/large_file.mp4"))
|> print_result.()

General.empty(client) |> print_result.()

({:error, {:appwrite_error, _, _, _}} = General.error400(client)) |> print_result.()
({:error, {:appwrite_error, _, _, _}} = General.error500(client)) |> print_result.()
({:error, {:appwrite_error, _, _, _}} = General.error502(client)) |> print_result.()

Query.equal("released", true) |> IO.puts
Query.equal("title", ["Spiderman", "Dr. Strange"]) |> IO.puts
Query.not_equal("title", "Spiderman") |> IO.puts
Query.less_than("releasedYear", 1990) |> IO.puts
Query.greater_than("releasedYear", 1990) |> IO.puts
Query.search("name", "john") |> IO.puts
Query.order_asc("title") |> IO.puts
Query.order_desc("title") |> IO.puts
Query.cursor_after("my_movie_id") |> IO.puts
Query.cursor_before("my_movie_id") |> IO.puts
Query.limit(50) |> IO.puts
Query.offset(20) |> IO.puts

Role.any |> Permission.read |> IO.puts
Role.user(Id.custom("userid")) |> Permission.write |> IO.puts
Role.users |> Permission.create |> IO.puts
Role.guests |> Permission.update |> IO.puts
Role.team("teamId", "owner") |> Permission.delete |> IO.puts
Role.team("teamId") |> Permission.delete |> IO.puts
Role.member("memberId") |> Permission.create |> IO.puts
Role.users("verified") |> Permission.update |> IO.puts
Role.user(Id.custom("userid"), "unverified") |> Permission.update |> IO.puts

Id.unique |> IO.puts
Id.custom("custom_id") |> IO.puts

General.headers(client) |> print_result.()
