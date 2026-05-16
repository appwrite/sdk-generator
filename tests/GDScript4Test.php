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
        'mkdir -p tests/sdks/gdscript/tests',
        'cp tests/languages/gdscript/test.gd tests/sdks/gdscript/tests/test.gd',
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/gdscript barichello/godot-ci:4.3 godot --headless --import --quit',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/gdscript barichello/godot-ci:4.3 godot --headless -s tests/sdks/gdscript/tests/tests.gd';

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
