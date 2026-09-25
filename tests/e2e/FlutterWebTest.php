<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Flutter;

/**
 * Flutter web e2e. The native (dart:io) Flutter e2e (FlutterStableTest) exercises push
 * over a raw TCP socket; a browser cannot open one, so the web build uses the
 * MqttBrowserClient transport over MQTT-over-WebSocket. This runs the web-only test
 * (web_tests.dart, no dart:io) under `flutter test --platform chrome`, where the
 * conditional import selects the browser client, against the mock broker's WebSocket
 * listener (ws://mqtt:8083/mqtt). Chrome is installed into the Flutter image for the run.
 */
final class FlutterWebTest extends Base
{
    #[Override]
    protected string $sdkName = 'flutter';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'flutter';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'flutter';
    #[Override]
    protected string $class = Flutter::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/flutter/test',
        'cp tests/e2e/languages/flutter/web_tests.dart tests/e2e/sdks/flutter/test/appwrite_web_test.dart',
        'cp tests/e2e/languages/flutter/run_web.sh tests/e2e/sdks/flutter/run_web.sh',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app:rw -w /app/tests/e2e/sdks/flutter ghcr.io/cirruslabs/flutter:stable sh run_web.sh';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PUSH_RESPONSES,
        ...Base::PUSH_ERROR_RESPONSES
    ];
}
