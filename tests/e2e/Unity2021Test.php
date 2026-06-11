<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language\Unity;
use Override;

final class Unity2021Test extends Base
{
    #[Override]
    protected string $sdkName = 'unity';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'unity';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'unity';
    #[Override]
    protected string $class = Unity::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/unity/Assets/Tests',
        'cp tests/e2e/languages/unity/Tests.cs tests/e2e/sdks/unity/Assets/Tests/Tests.cs',
        'cp tests/e2e/languages/unity/Tests.asmdef tests/e2e/sdks/unity/Assets/Tests/Tests.asmdef',
    ];

    #[Override]
    protected string $command = <<<'CMD'
docker run \
  --network="mockapi" \
  --rm \
  -v "$(pwd):/project" \
  -w /project/tests/e2e/sdks/unity \
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

    #[Override]
    public function testHTTPSuccess(): void
    {
        $GLOBALS['UNITY_TEST_MODE'] = true;
        parent::testHTTPSuccess();
    }

    public function tearDown(): void
    {
        unset($GLOBALS['UNITY_TEST_MODE']);
        parent::tearDown();
    }

    #[Override]
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
