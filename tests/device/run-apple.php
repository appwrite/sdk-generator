<?php

/**
 * Push device tests for the Apple SDK: drive the test app on an iOS Simulator through what the
 * unit tests cannot reach (a suspended or killed app, the Notification Service Extension filling
 * the placeholder APNs shows, a tap on a notification), against the mock broker in mock-server.
 *
 *     php tests/device/run-apple.php
 *
 * Needs the app built (tests/device/build.sh apple) and the mock broker's dependencies installed
 * (composer install in mock-server with COMPOSER=composer.mqtt.json), with Swoole. It boots a
 * Simulator when none is booted, and runs the broker itself, so one scenario can stop it. APNs
 * is simulated with `simctl push`, carrying the placeholder the server sends. Prints one
 * "Push device apple <scenario>:passed" or ":failed (...)" line per scenario and exits non-zero
 * when one failed.
 */

const BUNDLE = 'io.appwrite.pushdevice.apple';

// The device tests' user: the app subscribes to its topic, the mock keeps a persistent session for it.
const TOPIC = 'users/e2e-device-user';

// What the server sends through APNs: a placeholder the extension replaces with the message.
const PLACEHOLDER = 'New message';

$root = dirname(__DIR__, 2);
$products = "{$root}/tests/device/apps/apple/build/Build/Products";

function shell(string $command): string
{
    return (string) shell_exec($command . ' 2>/dev/null');
}

/** Wait up to $seconds for $condition to hold. */
function waitFor(callable $condition, int $seconds): bool
{
    $deadline = time() + $seconds;
    do {
        if ($condition()) {
            return true;
        }
        usleep(500_000);
    } while (time() < $deadline);

    return $condition();
}

/** The booted Simulator, booting the newest iPhone when none is. */
function simulator(): string
{
    $devices = json_decode(shell('xcrun simctl list devices available -j'), true)['devices'] ?? [];
    $iphones = [];
    foreach ($devices as $runtime => $list) {
        foreach ($list as $device) {
            if ($device['state'] === 'Booted') {
                return $device['udid'];
            }
            if (str_contains($runtime, 'iOS') && str_starts_with($device['name'], 'iPhone')) {
                $iphones[$runtime] = $device['udid'];
            }
        }
    }
    uksort($iphones, strnatcmp(...));
    $udid = (string) end($iphones);
    shell("xcrun simctl boot {$udid}");
    shell("xcrun simctl bootstatus {$udid} -b");

    return $udid;
}

$udid = simulator();

function simctl(string $arguments): string
{
    global $udid;
    [$command, $rest] = explode(' ', $arguments, 2) + [1 => ''];

    return shell("xcrun simctl {$command} {$udid} {$rest}");
}

$broker = null;

function startBroker(): bool
{
    global $broker, $root;
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        file_put_contents("{$root}/tests/device/broker.log", "start attempt {$attempt}\n", FILE_APPEND);
        $broker = proc_open(['php', "{$root}/mock-server/app/mqtt.php"], [0 => ['file', '/dev/null', 'r'], 1 => ['file', "{$root}/tests/device/broker.log", 'a'], 2 => ['file', "{$root}/tests/device/broker.log", 'a']], $pipes);
        if (waitFor(fn (): bool => brokerUp(), 30)) {
            return true;
        }
        proc_terminate($broker, 9);
        shell('pkill -9 -f mock-server/app/mqtt.php');
        proc_close($broker);
        sleep(2);
    }

    return false;
}

function stopBroker(): bool
{
    global $broker;
    if (is_resource($broker)) {
        proc_terminate($broker);
    }
    // Swoole's manager and worker processes outlive a master that does not stop in time.
    $stopped = waitFor(fn (): bool => !brokerUp(), 15);
    if (!$stopped) {
        shell('pkill -9 -f mock-server/app/mqtt.php');
        $stopped = waitFor(fn (): bool => !brokerUp(), 15);
    }
    if (is_resource($broker)) {
        proc_close($broker);
    }
    $broker = null;

    return $stopped;
}

function brokerUp(): bool
{
    $socket = @fsockopen('127.0.0.1', 1883, $code, $error, 1);
    if ($socket === false) {
        return false;
    }
    fclose($socket);

    return true;
}

function publish(string $title, string $saleId, bool $notification = true): void
{
    global $root;
    $payload = json_encode([
        'messageId' => 'device-apple-' . $saleId,
        ...($notification ? ['notification' => ['title' => $title, 'body' => 'Sale ' . $saleId]] : []),
        'data' => ['saleId' => $saleId],
    ]);
    shell('php ' . escapeshellarg("{$root}/mock-server/app/publish.php") . ' ' . escapeshellarg(TOPIC) . ' ' . escapeshellarg($payload));
}

/** Push the server's placeholder through the simulated APNs. */
function apns(): void
{
    $file = tempnam(sys_get_temp_dir(), 'apns');
    file_put_contents($file, json_encode(['aps' => ['alert' => ['title' => PLACEHOLDER], 'mutable-content' => 1]]));
    simctl('push ' . BUNDLE . ' ' . escapeshellarg($file));
    unlink($file);
}

// When the events read by events() start: the Simulator's log time of the last clearEvents().
$since = '';

function clearEvents(): void
{
    global $since;
    sleep(1);
    $since = trim(shell('date "+%Y-%m-%d %H:%M:%S"'));
}

/** What iOS logged about the app's notifications and its extension since $start. */
function notificationLog(string $start): array
{
    $predicate = '(subsystem == "com.apple.UserNotifications" AND eventMessage CONTAINS "io.appwrite.pushdevice" AND NOT eventMessage CONTAINS "Getting delivered") OR process == "NotificationService" OR eventMessage CONTAINS "io.appwrite.pushdevice.apple.service"';
    $log = simctl('spawn log show --style compact --start ' . escapeshellarg($start) . ' --predicate ' . escapeshellarg($predicate));
    $lines = array_values(array_filter(explode("\n", $log), fn (string $line): bool => preg_match('/^\d{4}-/', $line) === 1));

    return array_map(fn (string $line): string => substr($line, 11, 260), array_slice($lines, -60));
}

/** The app's and its extension's event lines since the last clearEvents(). */
function events(): array
{
    global $since;
    $lines = [];
    $log = simctl('spawn log show --style compact --start ' . escapeshellarg($since) . ' --predicate ' . escapeshellarg('(process == "PushDevice" OR process == "NotificationService") AND eventMessage CONTAINS "push-e2e: "'));
    foreach (explode("\n", $log) as $line) {
        if (preg_match('/push-e2e: (.*)$/', $line, $match)) {
            $lines[] = $match[1];
        }
    }

    return $lines;
}

function hasEvent(string $event): bool
{
    return in_array($event, events(), true);
}

function countEvent(string $event): int
{
    return count(array_keys(events(), $event, true));
}

/** The delivered notifications, as iOS stores them: on the test's Simulator, only the app posts any. */
function delivered(): string
{
    global $udid;
    $content = '';
    foreach (glob(getenv('HOME') . "/Library/Developer/CoreSimulator/Devices/{$udid}/data/Library/UserNotifications/*/DeliveredNotifications.plist") ?: [] as $store) {
        $content .= shell('plutil -p ' . escapeshellarg($store));
    }

    return $content;
}

function hasNotification(string $title): bool
{
    return str_contains(delivered(), '"' . $title . '"');
}

/** Run one step of UITests/Device.swift, with $environment for its arguments. */
function tapStep(string $test, array $environment = []): array
{
    global $products, $udid;
    $xctestrun = glob("{$products}/*.xctestrun")[0] ?? '';
    $variables = '';
    foreach ($environment as $name => $value) {
        $variables .= 'TEST_RUNNER_' . $name . '=' . escapeshellarg($value) . ' ';
    }
    exec("{$variables}xcodebuild test-without-building -xctestrun " . escapeshellarg($xctestrun) . " -destination id={$udid} -only-testing:UITests/Device/{$test} 2>&1", $output, $status);
    $failures = array_values(preg_grep('/error:|XCTAssert|XCTFail|failed/i', $output));

    return [$status === 0, array_slice($failures, 0, 5)];
}

function launch(): void
{
    simctl('launch ' . BUNDLE);
}

function terminate(): void
{
    simctl('terminate ' . BUNDLE);
    sleep(2);
}

/** Put the app in the background by bringing another app to the front. */
function background(): void
{
    simctl('launch com.apple.Preferences');
    sleep(2);
}

$failed = false;

function report(string $scenario, bool $passed, string $detail = ''): void
{
    global $failed;
    $failed = $failed || !$passed;
    echo "Push device apple {$scenario}:" . ($passed ? 'passed' : 'failed') . " ({$detail})\n";
}

// Each scenario returns [passed, detail].
$scenarios = [
    // A fresh install connects and subscribes, and the SDK asks for the notification permission.
    'launch' => function () use ($products): array {
        simctl('uninstall ' . BUNDLE);
        simctl('install ' . escapeshellarg("{$products}/Debug-iphonesimulator/PushDevice.app"));
        clearEvents();
        launch();
        [$allowed, $failures] = tapStep('testAllow');
        $subscribed = waitFor(fn (): bool => hasEvent('subscribed'), 60);
        $connected = waitFor(fn (): bool => hasEvent('connected'), 30);

        return [$allowed && $subscribed && $connected, json_encode(['allowed' => $allowed, 'failures' => $failures, 'events' => events()])];
    },

    // On screen, a message reaches the callback and posts no notification.
    'on screen' => function (): array {
        clearEvents();
        publish('Device on screen', 'screen');
        $received = waitFor(fn (): bool => hasEvent('message: Device on screen'), 20);
        sleep(3);
        $notified = hasNotification('Device on screen');

        return [$received && !$notified, json_encode(['received' => $received, 'notified' => $notified, 'events' => events()])];
    },

    // On screen, the placeholder APNs sends next to the MQTT message changes nothing: the
    // callback gets the message once, and no banner or placeholder is left.
    'placeholder while open' => function (): array {
        clearEvents();
        publish('Device open', 'open');
        apns();
        $received = waitFor(fn (): bool => hasEvent('message: Device open'), 20);
        sleep(15);
        $times = countEvent('message: Device open');
        $placeholder = hasNotification(PLACEHOLDER);
        $banner = hasNotification('Device open');

        return [$received && $times === 1 && !$placeholder && !$banner, json_encode(['times' => $times, 'placeholder' => $placeholder, 'banner' => $banner, 'events' => events()])];
    },

    // Suspended in the background: the extension fetches the message over MQTT and shows it in
    // place of the placeholder.
    'background' => function (): array {
        background();
        // Past the window in which the extension takes the app for live.
        sleep(12);
        clearEvents();
        $mark = $GLOBALS['since'];
        publish('Device background', 'background');
        apns();
        $shown = waitFor(fn (): bool => hasNotification('Device background'), 45);

        return [$shown, json_encode(['shown' => $shown, 'events' => events(), 'system' => notificationLog($mark)])];
    },

    // Tapping that notification brings the app back and reaches onNotificationOpened.
    'background tap' => function (): array {
        clearEvents();
        [$tapped, $failures] = tapStep('testOpen', ['TITLE' => 'Device background']);
        $opened = waitFor(fn (): bool => hasEvent('opened: background'), 30);

        return [$tapped && $opened, json_encode(['tapped' => $tapped, 'failures' => $failures, 'events' => events()])];
    },

    // Killed: the extension shows the message, and the tap that starts the app again reaches
    // it. The message the extension fetched reaches the callback once, with no second banner.
    'killed' => function (): array {
        terminate();
        clearEvents();
        $mark = $GLOBALS['since'];
        publish('Device killed', 'killed');
        apns();
        $shown = waitFor(fn (): bool => hasNotification('Device killed'), 45);
        $extension = events();
        clearEvents();
        [$tapped, $failures] = $shown ? tapStep('testOpen', ['TITLE' => 'Device killed']) : [false, []];
        $launched = waitFor(fn (): bool => hasEvent('launched: killed') || hasEvent('opened: killed'), 30);
        sleep(5);
        $times = countEvent('message: Device killed');

        return [$shown && $tapped && $launched && $times === 1, json_encode(['shown' => $shown, 'extension' => $extension, 'tapped' => $tapped, 'failures' => $failures, 'times' => $times, 'events' => events(), 'system' => notificationLog($mark)])];
    },

    // Killed while the broker is down: the extension cannot fetch, so the placeholder stays, and
    // the app removes it once it has subscribed again.
    'unfilled placeholder' => function (): array {
        terminate();
        $stopped = stopBroker();
        clearEvents();
        $mark = $GLOBALS['since'];
        apns();
        $placeholder = waitFor(fn (): bool => hasNotification(PLACEHOLDER), 60);
        $extension = events();
        $started = startBroker();
        clearEvents();
        launch();
        $subscribed = waitFor(fn (): bool => hasEvent('subscribed'), 60);
        $cleared = waitFor(fn (): bool => !hasNotification(PLACEHOLDER), 30);

        return [$stopped && $placeholder && $started && $subscribed && $cleared, json_encode(['stopped' => $stopped, 'placeholder' => $placeholder, 'extension' => $extension, 'started' => $started, 'subscribed' => $subscribed, 'cleared' => $cleared, 'events' => events(), 'system' => notificationLog($mark)])];
    },
];

if (!startBroker()) {
    fwrite(STDERR, "the mock broker did not start, see tests/device/broker.log\n");
    exit(1);
}
foreach ($scenarios as $name => $scenario) {
    try {
        [$passed, $detail] = $scenario();
    } catch (Throwable $error) {
        [$passed, $detail] = [false, $error->getMessage()];
    }
    report($name, $passed, $detail);
}
stopBroker();

exit($failed ? 1 : 0);
