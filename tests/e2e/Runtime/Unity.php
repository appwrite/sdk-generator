<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Unity
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'Unity2021' => new Runtime(
            directory: 'unity',
            language: new Language\Unity()->setTestMode(true),
            name: 'unity',
            platform: 'client',
            header: 'unity',
            build: [
                'mkdir -p tests/e2e/sdks/unity/Assets/Tests',
                'cp tests/e2e/languages/unity/Tests.cs tests/e2e/sdks/unity/Assets/Tests/Tests.cs',
                'cp tests/e2e/languages/unity/CookieTransportTests.cs tests/e2e/sdks/unity/Assets/Tests/CookieTransportTests.cs',
                'cp tests/e2e/languages/unity/Tests.asmdef tests/e2e/sdks/unity/Assets/Tests/Tests.asmdef',
            ],
            command: <<<'CMD'
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

            status=0
            /opt/unity/Editor/Unity \
              -projectPath . \
              -batchmode \
              -nographics \
              -runTests \
              -testPlatform PlayMode \
              -stackTraceLogType None \
              -logFile unity.log \
              2>/dev/null || status=$?

            if [ -s result.txt ]; then
              cat result.txt
            else
              echo "Test Started"
              echo "Unity result output missing"
              ls -la
              [ -f unity.log ] && cat unity.log
              [ -f /root/.config/unity3d/Editor.log ] && cat /root/.config/unity3d/Editor.log
              status=1
            fi

            exit "${status}"
          '
        CMD,
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::GENERAL_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::QUERY_ENCODING_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::BOUNDARY_UPLOAD_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                ...Output::COOKIE_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::QUERY_PAGE_OVERFLOW_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::UNITY_MANAGER_RESPONSES,
            ],
        );
    }
}
