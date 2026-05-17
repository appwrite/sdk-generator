extends SceneTree

var _Appwrite = load("res://addons/appwrite/appwrite.gd")
var Appwrite = _Appwrite.new()

func _init() -> void:
    call_deferred("start")

func start() -> void:
    await run_tests()
    print("\nAll tests completed")
    quit()

func run_tests() -> void:
    Appwrite.add_header("Origin", "http://localhost:8080") \
        .set_project("123456") \
        .set_self_signed(true) \
        .set_endpoint("http://localhost:8080/v1")

    print("\nTest Started")

    var sdk_headers = Appwrite.get_headers()
    print(
        "x-sdk-name: %s; x-sdk-platform: %s; x-sdk-language: %s; x-sdk-version: %s"
        % [
            sdk_headers.get("x-sdk-name", ""),
            sdk_headers.get("x-sdk-platform", ""),
            sdk_headers.get("x-sdk-language", ""),
            sdk_headers.get("x-sdk-version", "")
        ]
    )

    print_response(await Appwrite.ping())
    await run_foo_tests()
    await run_bar_tests()
    await run_general_tests()

    run_query_tests()
    run_permission_tests()
    run_id_tests()
    run_operator_tests()

func print_response(response) -> void:
    if response == null:
        print("null response")
        return

    if response is AppwriteException:
        print(response.message)
        print(response.response)
        return

    if response is Dictionary:
        if response.has("result"):
            print(response["result"])
        else:
            print(response)

        return

    if "result" in response:
        print(response.result)
        return

    print(response)

func run_foo_tests() -> void:
    print_response(await Appwrite.foo.xget("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.post("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.put("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.patch("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.delete("string", 123, ["string in array"]))

func run_bar_tests() -> void:
    print_response(await Appwrite.bar.xget("string", 123, ["string in array"]))
    print_response(await Appwrite.bar.post("string", 123, ["string in array"]))
    print_response(await Appwrite.bar.put("string", 123, ["string in array"]))
    print_response(await Appwrite.bar.patch("string", 123, ["string in array"]))
    print_response(await Appwrite.bar.delete("string", 123, ["string in array"]))

func run_general_tests() -> void:
    var response

    response = await Appwrite.general.redirect()
    print_response(response)

    response = await Appwrite.general.upload(
        "string",
        123,
        ["string in array"],
        AppwriteInputFile.from_path("res://tests/resources/file.png")
    )

    print_response(response)

    response = await Appwrite.general.upload(
        "string",
        123,
        ["string in array"],
        AppwriteInputFile.from_path("res://tests/resources/large_file.mp4")
    )

    print_response(response)

    var file_bytes := FileAccess.get_file_as_bytes(
        "res://tests/resources/file.png"
    )

    response = await Appwrite.general.upload(
        "string",
        123,
        ["string in array"],
        AppwriteInputFile.from_bytes(
            file_bytes,
            "file.png",
        )
    )

    print_response(response)

    var large_file_bytes := FileAccess.get_file_as_bytes(
        "res://tests/resources/large_file.mp4"
    )

    response = await Appwrite.general.upload(
        "string",
        123,
        ["string in array"],
        AppwriteInputFile.from_bytes(
            large_file_bytes,
            "large_file.mp4",
        )
    )

    print_response(response)

    response = await Appwrite.general.xenum(Appwrite.MOCKTYPE.FIRST)
    print_response(response)

    response = await Appwrite.general.create_player(
        AppwritePlayer.from_dict({
            "id": "player1",
            "name": "John Doe",
            "score": 100
        })
    )

    print_response(response)

    response = await Appwrite.general.create_players([
        AppwritePlayer.from_dict({
            "id": "player1",
            "name": "John Doe",
            "score": 100
        }),
        AppwritePlayer.from_dict({
            "id": "player2",
            "name": "Jane Doe",
            "score": 200
        })
    ] as Array[AppwritePlayer])

    print_response(response)

    await test_errors()

    await Appwrite.general.empty()

    var url = await Appwrite.general.oauth2(
        "clientId",
        ["test"],
        "123456",
        "https://localhost:8080",
        "https://localhost:8080"
    )

    print(url)

    response = await Appwrite.general.headers()
    print_response(response)

func test_errors() -> void:
    var response

    response = await Appwrite.general.error400()
    print_response(response)

    response = await Appwrite.general.error500()
    print_response(response)

    response = await Appwrite.general.error502()
    print_response(response)

    response = Appwrite.set_endpoint("htp://cloud.appwrite.io/v1")
    print_response(response)

func run_query_tests() -> void:
    print(AppwriteQuery.equal("released", [true]))
    print(AppwriteQuery.equal("title", ["Spiderman", "Dr. Strange"]))
    print(AppwriteQuery.not_equal("title", "Spiderman"))

    print(AppwriteQuery.less_than("releasedYear", 1990))
    print(AppwriteQuery.greater_than("releasedYear", 1990))

    print(AppwriteQuery.search("name", "john"))

    print(AppwriteQuery.is_null("name"))
    print(AppwriteQuery.is_not_null("name"))

    print(AppwriteQuery.limit(50))
    print(AppwriteQuery.offset(20))

    print(AppwriteQuery.order_asc("title"))
    print(AppwriteQuery.order_desc("title"))

    print(AppwriteQuery.contains("title", "Spider"))

func run_permission_tests() -> void:
    print(AppwritePermission.read(AppwriteRole.any()))

    print(
        AppwritePermission.write(
            AppwriteRole.user(AppwriteID.custom("userid"))
        )
    )

    print(AppwritePermission.create(AppwriteRole.users()))

    print(
        AppwritePermission.delete(
            AppwriteRole.team("teamId", "owner")
        )
    )

func run_id_tests() -> void:
    print(AppwriteID.unique())
    print(AppwriteID.custom("custom_id"))

func run_operator_tests() -> void:
    print(AppwriteOperator.increment())
    print(AppwriteOperator.increment(5, 100))
    print(AppwriteOperator.decrement())
    print(AppwriteOperator.decrement(3, 0))
    print(AppwriteOperator.multiply(2))
    print(AppwriteOperator.multiply(3, 1000))
    print(AppwriteOperator.divide(2))
    print(AppwriteOperator.divide(4, 1))
    print(AppwriteOperator.modulo(5))
    print(AppwriteOperator.power(2))
    print(AppwriteOperator.power(3, 100))
    print(AppwriteOperator.array_append(['item1', 'item2']))
    print(AppwriteOperator.array_prepend(['first', 'second']))
    print(AppwriteOperator.array_insert(0, 'newItem'))
    print(AppwriteOperator.array_remove('oldItem'))
    print(AppwriteOperator.array_unique())
    print(AppwriteOperator.array_intersect(['a', 'b', 'c']))
    print(AppwriteOperator.array_diff(['x', 'y']))
    print(AppwriteOperator.array_filter(AppwriteOperator.EQUAL, 'test'))
    print(AppwriteOperator.string_concat('suffix'))
    print(AppwriteOperator.string_replace('old', 'new'))
    print(AppwriteOperator.toggle())
    print(AppwriteOperator.date_add_days(7))
    print(AppwriteOperator.date_sub_days(3))
    print(AppwriteOperator.date_set_now())

    var headers = Appwrite.get_headers()
    print(headers)
