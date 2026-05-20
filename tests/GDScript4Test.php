<?php

namespace Tests;

class GDScript4Test extends Base
{
    protected string $sdkName = 'gdscript';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'gdscript';
    protected string $version = '0.0.1';

    protected string $language = 'gdscript';
    protected string $class = 'Appwrite\SDK\Language\GDScript';
    protected array $build = [
        'cp tests/languages/gdscript/test.gd tests/sdks/gdscript/test.gd',
        'cp -r tests/resources/ tests/sdks/gdscript/tests/',
        'docker run --rm \
        -v $(pwd)/tests/sdks/gdscript:/app \
        -w /app \
        barichello/godot-ci:4.6 \
        godot --headless --import --quit'
    ];
    protected string $command =
        'docker run --network="mockapi" --rm \
        -v $(pwd)/tests/sdks/gdscript:/app \
        -w /app \
        barichello/godot-ci:4.6 \
        godot --headless --script test.gd';

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}
