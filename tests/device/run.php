<?php

/**
 * Push device tests: drive a Push test app on a running Android emulator through the situations
 * that unit and Robolectric tests cannot reach (a killed process, an app removed from recents, a
 * lost network, the system permission screens), against the mock broker in mock-server.
 *
 *     php tests/device/run.php <android|react-native|flutter>
 *
 * Needs adb with one emulator attached, the test app for that SDK installed (see build.sh), and
 * the mock broker running (docker compose up -d mqtt in mock-server). Prints one
 * "Push device <sdk> <scenario>:passed" or ":failed (...)" line per scenario and exits non-zero
 * when one failed.
 */

const PACKAGES = [
    'android' => 'io.appwrite.pushdevice.android',
    'react-native' => 'io.appwrite.pushdevice.reactnative',
    'flutter' => 'io.appwrite.pushdevice.flutter',
];

// The device tests' user: the apps subscribe to its topic, the mock keeps a persistent session for it.
const TOPIC = 'users/e2e-device-user';

$sdk = $argv[1] ?? '';
if (!isset(PACKAGES[$sdk])) {
    fwrite(STDERR, "usage: php tests/device/run.php <" . implode('|', array_keys(PACKAGES)) . ">\n");
    exit(2);
}
$package = PACKAGES[$sdk];

function shell(string $command): string
{
    return (string) shell_exec($command . ' 2>/dev/null');
}

function adb(string $arguments): string
{
    return shell('adb ' . $arguments);
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

/** The app's event lines since the log was last cleared: native, React Native and Flutter. */
function events(): array
{
    $lines = [];
    foreach (explode("\n", adb('logcat -d -v tag')) as $line) {
        if (preg_match("/push-e2e:?\\s+'?(.*?)'?\\s*$/", $line, $match)) {
            $lines[] = $match[1];
        }
    }

    return $lines;
}

function clearEvents(): void
{
    adb('logcat -c');
}

function hasEvent(string $event): bool
{
    return in_array($event, events(), true);
}

function publish(string $title, string $saleId): void
{
    $payload = json_encode([
        'messageId' => 'device-' . $saleId,
        'notification' => ['title' => $title, 'body' => 'Sale ' . $saleId],
        'data' => ['saleId' => $saleId],
    ]);
    shell('docker exec mqtt php app/publish.php ' . escapeshellarg(TOPIC) . ' ' . escapeshellarg($payload));
}

/** Titles of the notifications $package has posted. */
function notifications(string $package): array
{
    $titles = [];
    $record = null;
    foreach (explode("\n", adb('shell dumpsys notification --noredact')) as $line) {
        if (str_contains($line, 'NotificationRecord(')) {
            $record = str_contains($line, 'pkg=' . $package . ' ');
        } elseif ($record && preg_match('/android\.title=\w+ \((.*)\)/', $line, $match)) {
            $titles[] = $match[1];
        }
    }

    return $titles;
}

// Why the last screen dump failed, for a failing scenario's detail.
$dumpError = '';

/** The on-screen elements: [label, centre x, centre y], labels from text and content descriptions. */
function screen(): array
{
    global $dumpError;
    $xml = '';
    // A dump fails while the screen is changing ("could not get idle state"); try again.
    for ($attempt = 0; $attempt < 3 && !str_contains($xml, '<hierarchy'); $attempt++) {
        adb('shell rm -f /sdcard/ui.xml');
        $output = (string) shell_exec('adb shell uiautomator dump /sdcard/ui.xml 2>&1');
        $xml = adb('shell cat /sdcard/ui.xml');
        $dumpError = str_contains($xml, '<hierarchy') ? '' : trim($output);
        if ($dumpError !== '') {
            sleep(1);
        }
    }
    $nodes = [];
    preg_match_all('/<node [^>]*>/', $xml, $matches);
    foreach ($matches[0] as $node) {
        if (!preg_match('/bounds="\[(\d+),(\d+)\]\[(\d+),(\d+)\]"/', $node, $bounds)) {
            continue;
        }
        preg_match_all('/(?:text|content-desc)="([^"]*)"/', $node, $labels);
        foreach ($labels[1] as $label) {
            if ($label !== '') {
                $nodes[] = [html_entity_decode($label), intdiv($bounds[1] + $bounds[3], 2), intdiv($bounds[2] + $bounds[4], 2)];
            }
        }
    }

    return $nodes;
}

/** What is on screen, and why the last dump failed: for a failing scenario's detail. */
function seen(): array
{
    global $dumpError, $tapped;

    return ['tapped' => $tapped, 'top' => topActivity(), 'screen' => array_slice(array_column(screen(), 0), 0, 20), 'dumpError' => $dumpError];
}

// The last element tap() tapped, for a failing scenario's detail.
$tapped = '';

function tap(string $pattern): bool
{
    global $tapped;
    foreach (screen() as [$label, $x, $y]) {
        if (preg_match($pattern, (string) $label)) {
            $tapped = "{$label} at {$x},{$y}";
            adb("shell input tap {$x} {$y}");

            return true;
        }
    }

    return false;
}

function tapNotification(string $title): bool
{
    adb('shell cmd statusbar expand-notifications');
    sleep(2);
    $tapped = tap('/^' . preg_quote($title, '/') . '$/');
    if (!$tapped) {
        adb('shell cmd statusbar collapse');
    }

    return $tapped;
}

function topActivity(): string
{
    preg_match('/topResumedActivity=\S+ \S+ (\S+)/', adb('shell dumpsys activity activities'), $match);

    return $match[1] ?? '';
}

function launch(string $package): void
{
    adb("shell monkey -p {$package} -c android.intent.category.LAUNCHER 1");
}

function home(): void
{
    adb('shell input keyevent KEYCODE_HOME');
    sleep(2);
}

function removeFromRecents(string $package): void
{
    foreach (explode("\n", adb('shell dumpsys activity recents')) as $line) {
        if (str_contains($line, 'Recent #') && str_contains($line, $package) && preg_match('/Task\{\w+ #(\d+)/', $line, $match)) {
            adb('shell am stack remove ' . $match[1]);
        }
    }
}

function inRecents(string $package): bool
{
    return array_any(explode("\n", adb('shell dumpsys activity recents')), fn($line): bool => str_contains((string) $line, 'Recent #') && str_contains((string) $line, $package));
}

function network(bool $on): void
{
    $state = $on ? 'enable' : 'disable';
    adb("shell svc wifi {$state}");
    adb("shell svc data {$state}");
}

$failed = false;

function report(string $sdk, string $scenario, bool $passed, string $detail = ''): void
{
    global $failed;
    $failed = $failed || !$passed;
    echo "Push device {$sdk} {$scenario}:" . ($passed ? 'passed' : "failed ({$detail})") . "\n";
}

// Each scenario returns [passed, detail].
$scenarios = [
    // A fresh install signs in, connects and subscribes, and the SDK asks for the notification
    // permission on its own.
    'launch' => function () use ($package): array {
        adb("shell pm clear {$package}");
        clearEvents();
        launch($package);
        $subscribed = waitFor(fn (): bool => hasEvent('subscribed'), 60);
        $prompted = waitFor(fn (): bool => tap('/^Allow$/'), 15);
        sleep(2);
        $granted = str_contains(adb("shell dumpsys package {$package}"), 'POST_NOTIFICATIONS: granted=true');

        // A freshly booted emulator can take a moment to reach the broker; the SDK retries.
        $connected = waitFor(fn (): bool => hasEvent('connected'), 60);

        return [$subscribed && $connected && $prompted && $granted, json_encode(['events' => events(), 'prompted' => $prompted, 'granted' => $granted, ...seen()])];
    },

    // On screen, a message reaches the callback and posts no notification.
    'on screen' => function () use ($package): array {
        clearEvents();
        publish('Device on screen', 'screen');
        $received = waitFor(fn (): bool => hasEvent('message: Device on screen'), 20);
        sleep(3);
        $notified = in_array('Device on screen', notifications($package), true);

        return [$received && !$notified, json_encode(['received' => $received, 'notified' => $notified])];
    },

    // Exact alarms: the request opens the system screen; once allowed, the status reports it.
    'exact alarms' => function (): array {
        clearEvents();
        $asked = tap('/^Allow exact alarms$/i');
        $settings = waitFor(fn (): bool => str_contains(topActivity(), 'com.android.settings'), 10);
        $afterTap = seen();
        $allowed = $settings && waitFor(fn (): bool => tap('/^Allow setting alarms/i'), 10);
        sleep(2);
        // Back to the app only from Settings: from the app, Back would leave it.
        if ($settings) {
            adb('shell input keyevent KEYCODE_BACK');
            sleep(2);
        }
        tap('/^Check background status$/i');
        $reported = waitFor(fn (): bool => (bool) preg_grep('/^status: exact=true/', events()), 10);

        return [$asked && $settings && $allowed && $reported, json_encode(['asked' => $asked, 'settings' => $settings, 'allowed' => $allowed, 'events' => events(), 'afterTap' => $afterTap])];
    },

    // The battery-optimisation exemption: the request shows the system dialog; once allowed, the
    // status reports it.
    'battery exemption' => function (): array {
        clearEvents();
        $asked = tap('/^Ignore battery optimi/i');
        $allowed = waitFor(fn (): bool => tap('/^Allow$/'), 10);
        sleep(2);
        tap('/^Check background status$/i');
        $reported = waitFor(fn (): bool => (bool) preg_grep('/^status: .*battery=true/', events()), 10);

        return [$asked && $allowed && $reported, json_encode(['asked' => $asked, 'allowed' => $allowed, 'events' => events(), ...seen()])];
    },

    // In the background: a notification is posted, and tapping it reaches onNotificationOpened.
    'background tap' => function () use ($package): array {
        home();
        clearEvents();
        publish('Device background', 'background');
        $posted = waitFor(fn (): bool => in_array('Device background', notifications($package), true), 30);
        $tapped = $posted && tapNotification('Device background');
        $opened = waitFor(fn (): bool => hasEvent('opened: background'), 20);

        return [$posted && $tapped && $opened, json_encode(['posted' => $posted, 'tapped' => $tapped, 'events' => events()])];
    },

    // Killed but still in recents: a scheduled wake-up posts the message, and the tap that starts
    // the app again is reported by getInitialNotification.
    'killed in recents' => function () use ($package): array {
        home();
        adb("shell am kill {$package}");
        sleep(2);
        publish('Device killed', 'killed');
        $posted = waitFor(fn (): bool => in_array('Device killed', notifications($package), true), 150);
        clearEvents();
        $tapped = $posted && tapNotification('Device killed');
        $launched = waitFor(fn (): bool => hasEvent('launched: killed'), 60);

        return [$posted && $tapped && $launched, json_encode(['posted' => $posted, 'tapped' => $tapped, 'events' => events()])];
    },

    // Removed from recents and killed: the same, with no task left to restore.
    'removed from recents' => function () use ($package): array {
        home();
        removeFromRecents($package);
        adb("shell am kill {$package}");
        sleep(2);
        $removed = !inRecents($package);
        publish('Device removed', 'removed');
        $posted = waitFor(fn (): bool => in_array('Device removed', notifications($package), true), 150);
        clearEvents();
        $tapped = $posted && tapNotification('Device removed');
        $launched = waitFor(fn (): bool => hasEvent('launched: removed'), 60);

        return [$removed && $posted && $tapped && $launched, json_encode(['removed' => $removed, 'posted' => $posted, 'tapped' => $tapped, 'events' => events()])];
    },

    // Killed while offline: nothing arrives without a network, and everything sent meanwhile is
    // replayed once it is back.
    'offline replay' => function () use ($package): array {
        home();
        adb("shell am kill {$package}");
        sleep(3);
        network(false);
        sleep(5);
        foreach (['1', '2', '3'] as $n) {
            publish("Device offline {$n}", "offline-{$n}");
        }
        sleep(15);
        $whileOffline = array_values(preg_grep('/^Device offline/', notifications($package)));
        network(true);
        $replayed = waitFor(fn (): bool => count(preg_grep('/^Device offline/', notifications($package))) === 3, 180);

        return [$whileOffline === [] && $replayed, json_encode(['whileOffline' => $whileOffline, 'after' => notifications($package)])];
    },

    // Force-stopped: Android cancels the app's wake-ups, so nothing arrives; opening the app
    // again replays what was sent meanwhile, to the callback, or as a notification when the
    // saved background delivery resumes before the app has subscribed again.
    'force-stopped' => function () use ($package): array {
        adb("shell am force-stop {$package}");
        // The stopped process may still hold its connection for a moment; publish once it is gone.
        waitFor(fn (): bool => trim(adb("shell pidof {$package}")) === '', 10);
        sleep(2);
        publish('Device stopped', 'stopped');
        sleep(45);
        $whileStopped = in_array('Device stopped', notifications($package), true);
        clearEvents();
        launch($package);
        $replayed = waitFor(fn (): bool => hasEvent('message: Device stopped') || in_array('Device stopped', notifications($package), true), 60);

        return [!$whileStopped && $replayed, json_encode(['whileStopped' => $whileStopped, 'events' => events(), 'notifications' => notifications($package)])];
    },
];

network(true);
foreach ($scenarios as $name => $scenario) {
    try {
        [$passed, $detail] = $scenario();
    } catch (Throwable $error) {
        [$passed, $detail] = [false, $error->getMessage()];
    }
    report($sdk, $name, $passed, $detail);
}
network(true);

exit($failed ? 1 : 0);
