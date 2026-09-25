<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\ReactNative;

/**
 * React Native push e2e. The main RN e2e (ReactNativeTest) runs the SDK in a browser
 * (react-native-web + Playwright), which cannot open the raw TCP socket the RN Push service
 * needs. This split-out test runs the generated Push client under Node against the mock
 * broker's TCP listener (mqtt:1883): rollup.push.config.mjs bundles push.node.js with
 * `react-native-tcp-socket` aliased to a Node net/tls adapter, so the real
 * src/services/push.ts + src/lib/tcp-stream.ts execute over a real socket. Mirrors how
 * FlutterWebTest is split out from FlutterStableTest.
 */
final class ReactNativePushTest extends Base
{
    #[Override]
    protected string $sdkName = 'react-native';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'reactnative';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'react-native';
    #[Override]
    protected string $class = ReactNative::class;

    #[Override]
    protected array $build = [
        'cp tests/e2e/languages/react-native/push.node.js tests/e2e/sdks/react-native/push.node.js',
        'cp tests/e2e/languages/react-native/rollup.push.config.mjs tests/e2e/sdks/react-native/rollup.push.config.mjs',
        'cp -R tests/e2e/languages/react-native/shims tests/e2e/sdks/react-native/shims',
        // Install deps without peers (react-native-tcp-socket is an optional peer that would
        // not install anyway), then drop the Node shims for the native peers into node_modules
        // so the generated push transport resolves them at runtime, then bundle for Node.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 sh -c "npm install --omit=peer && npm install --no-save @rollup/plugin-commonjs @rollup/plugin-node-resolve @rollup/plugin-replace && rm -rf node_modules/react-native node_modules/react-native-tcp-socket && cp -R shims/react-native node_modules/react-native && cp -R shims/react-native-tcp-socket node_modules/react-native-tcp-socket && npx rollup -c rollup.push.config.mjs"',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 node dist/push.node.bundle.cjs';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PUSH_RESPONSES
    ];
}
