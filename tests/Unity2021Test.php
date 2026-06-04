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

    protected string $command = <<<'CMD'
docker run \
  --network="mockapi" \
  --rm \
  -v "$(pwd):/project" \
  -w /project/tests/sdks/unity \
  -e UNITY_LICENSE \
  unityci/editor:ubuntu-2021.3.45f1-base-3.1.0 \
  /bin/bash -lc '
    license_path="/tmp/Unity_lic.ulf"

    rm -f result.txt
    trap "rm -f ${license_path}" EXIT

    printf "%s" "${UNITY_LICENSE}" > "${license_path}"

    /opt/unity/Editor/Unity \
      -nographics \
      -batchmode \
      -manualLicenseFile "${license_path}" \
      -quit || true

    /opt/unity/Editor/Unity \
      -projectPath . \
      -batchmode \
      -nographics \
      -runTests \
      -testPlatform PlayMode \
      -stackTraceLogType None \
      -logFile unity.log \
      2>/dev/null

    if [ -s result.txt ]; then
      cat result.txt
    else
      echo "Test Started"
      echo "Unity result output missing"
      ls -la
      [ -f unity.log ] && cat unity.log
      [ -f /root/.config/unity3d/Editor.log ] && cat /root/.config/unity3d/Editor.log
    fi
  '
CMD;

    public function testHTTPSuccess(): void
    {
        $license = \getenv('UNITY_LICENSE');
        if ($license === false || \trim($license) === '') {
            $this->markTestSkipped('UNITY_LICENSE is required to run Unity SDK tests.');
        }

        // Set Unity test mode to exclude problematic files
        $GLOBALS['UNITY_TEST_MODE'] = true;

        parent::testHTTPSuccess();
    }

    public function tearDown(): void
    {
        unset($GLOBALS['UNITY_TEST_MODE']);
        parent::tearDown();
    }

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}
