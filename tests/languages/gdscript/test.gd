extends SceneTree 

func _init() -> void:
    call_deferred("start")


func start() -> void:
    await run_tests()

    print("\nAll tests completed")

    quit()


func run_tests() -> void:
    Appwrite.add_header("Origin", "http://localhost:8080")
    Appwrite.set_self_signed(true)

    Appwrite.set_endpoint("http://localhost:8080/v1")

    print("\nTest Started")

    var sdk_headers := Appwrite.get_headers()

    print(
        "x-sdk-name: %s; x-sdk-platform: %s; x-sdk-language: %s; x-sdk-version: %s"
        % [
            sdk_headers.get("x-sdk-name", ""),
            sdk_headers.get("x-sdk-platform", ""),
            sdk_headers.get("x-sdk-language", ""),
            sdk_headers.get("x-sdk-version", "")
        ]
    )

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
    print_response(await Appwrite.foo.get("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.post("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.put("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.patch("string", 123, ["string in array"]))
    print_response(await Appwrite.foo.delete("string", 123, ["string in array"]))


func run_bar_tests() -> void:
    print_response(await Appwrite.bar.get("string", 123, ["string in array"]))
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
            "image/png"
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
            "video/mp4"
        )
    )

    print_response(response)

    response = await Appwrite.general.enum(Appwrite.MOCKTYPE.FIRST)
    print_response(response)

    response = await Appwrite.general.create_player(
        AppwritePlayer.new({
            "id": "player1",
            "name": "John Doe",
            "score": 100
        })
    )

    print_response(response)

    response = await Appwrite.general.create_players([
        {
            "id": "player1",
            "name": "John Doe",
            "score": 100
        },
        {
            "id": "player2",
            "name": "Jane Doe",
            "score": 200
        }
    ])

    print_response(response)

    await test_errors()

    await Appwrite.general.empty()

    var url := Appwrite.general.oauth2(
        "clientId",
        ["test"],
        "123456",
        "https://localhost",
        "https://localhost"
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

    var invalid_result = client.set_endpoint("htp://cloud.appwrite.io/v1")

    if invalid_result is AppwriteException:
        print(invalid_result.message)
    else:
        print(invalid_result)


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

    print(AppwriteQuery.or_queries([
        AppwriteQuery.equal("released", true),
        AppwriteQuery.less_than("releasedYear", 1990)
    ]))


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
    print(Operator.increment())
    print(Operator.increment(5, 100))
    print(Operator.decrement())
    print(Operator.decrement(3, 0))
    print(Operator.multiply(2))
    print(Operator.multiply(3, 1000))
    print(Operator.divide(2))
    print(Operator.divide(4, 1))
    print(Operator.modulo(5))
    print(Operator.power(2))
    print(Operator.power(3, 100))
    print(Operator.array_append(['item1', 'item2']))
    print(Operator.array_prepend(['first', 'second']))
    print(Operator.array_insert(0, 'newItem'))
    print(Operator.array_remove('oldItem'))
    print(Operator.array_unique())
    print(Operator.array_intersect(['a', 'b', 'c']))
    print(Operator.array_diff(['x', 'y']))
    print(Operator.array_filter(Condition.EQUAL, 'test'))
    print(Operator.string_concat('suffix'))
    print(Operator.string_replace('old', 'new'))
    print(Operator.toggle())
    print(Operator.date_add_days(7))
    print(Operator.date_sub_days(3))
    print(Operator.date_set_now())

    response = general.headers()
    print(response.result)
