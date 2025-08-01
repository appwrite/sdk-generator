<?php

namespace Tests;

class Unity2021Test extends Base
{
    protected string $sdkName = 'unity';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'unity';
    protected string $version = '0.0.1';

    protected string $language = 'unity';
    protected string $class = 'Appwrite\SDK\Language\Unity';
    protected array $build = [
        'mkdir -p tests/sdks/unity/Assets/Tests',
        'cp tests/languages/unity/Tests.cs tests/sdks/unity/Assets/Tests/Tests.cs',
        'cp tests/languages/unity/Tests.asmdef tests/sdks/unity/Assets/Tests/Tests.asmdef',
    ];

    protected string $command = 'docker run --network="mockapi" --rm -v "$(pwd):/project" -w /project/tests/sdks/unity -e UNITY_LICENSE unityci/editor:ubuntu-2021.3.45f1-base-3.1.0 /bin/bash -c "echo \"\$UNITY_LICENSE\" > Unity_lic.ulf && /opt/unity/Editor/Unity -nographics -batchmode -manualLicenseFile Unity_lic.ulf -quit || true && /opt/unity/Editor/Unity -projectPath . -batchmode -nographics -runTests -testPlatform PlayMode -stackTraceLogType None -logFile - 2>/dev/null | sed -n \'/Test Started/,\$p\' | grep -v -E \'^(UnityEngine\\.|System\\.|Cysharp\\.|\\(Filename:|\\[.*\\]|##utp:|^\\s*\$|The header Origin is managed automatically|Connected to realtime:)\' | grep -v \'StackTraceUtility\'"';

    public function testHTTPSuccess(): void
    {
        // Set Unity test mode to exclude problematic files
        $GLOBALS['UNITY_TEST_MODE'] = true;

        parent::testHTTPSuccess();
    }

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}
