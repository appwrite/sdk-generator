<?php

namespace Tests;

class Godot4Test extends Base
{
    protected string $sdkName = 'godot';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'godot';
    protected string $version = '0.0.1';

    protected string $language = 'godot';
    protected string $class = 'Appwrite\SDK\Language\Godot';

    protected array $build = [
        'cp tests/languages/godot/test.gd tests/sdks/godot/tests/test.gd',
        'cp -r tests/resources/ tests/sdks/godot/tests/',
        'docker run --rm \
        -v $(pwd)/tests/sdks/godot:/app \
        -w /app \
        barichello/godot-ci:4.6 \
        godot --headless --import --quit'
    ];

    protected string $command =
        'docker run --network="mockapi" --rm \
        -v $(pwd)/tests/sdks/godot:/app \
        -w /app \
        barichello/godot-ci:4.6 \
        godot --headless --script tests/test.gd';

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}
