# Push device tests

Scenarios that need a real Android system, run on an emulator in CI
(`.github/workflows/push-device.yml`: nightly, on demand, and on pull requests labelled
`push-device-tests`):

| Scenario | Checks |
|---|---|
| launch | the app connects and subscribes, and the SDK asks for the notification permission |
| on screen | a message reaches the callback and posts no notification |
| exact alarms | `requestExactAlarms()` opens the system screen; once allowed, `backgroundStatus()` reports it |
| battery exemption | `requestIgnoreBatteryOptimizations()` shows the dialog; once allowed, the status reports it |
| reconnect while running | a lost network reports `onClose`, the SDK reconnects on its own (`onOpen`, no refused credential), and messages arrive again |
| background tap | a notification with the push's title and body (not the raw payload) is posted, and its tap reaches `onNotificationOpened` |
| killed in recents | a scheduled wake-up posts the message; the tap that restarts the app reaches `getInitialNotification` |
| removed from recents | the same with no task left |
| repeated wake-ups | with the app closed, a second message a minute later arrives from a later wake-up |
| offline replay | nothing arrives without a network; everything sent meanwhile arrives once it is back |
| doze | a message sent while the device is in Doze is not lost and arrives once it leaves Doze |
| force-stopped | nothing arrives; opening the app again replays it |
| reboot | after a reboot, delivery resumes without opening the app |

The checks wait for what they assert with generous deadlines rather than fixed delays, so a
failure means a wake-up, the notification path or the connection broke, not that CI was slow.

Each SDK has a test app in `apps/` (native Android, React Native, Flutter) built against the SDK
generated from the test spec. The apps use the mock broker (`mock-server`) as the
`e2e-device-user`, whose topic the broker keeps a persistent session for, and log one event per
line, prefixed with `push-e2e`.

```bash
tests/device/build.sh android                 # generate the SDK and build tests/device/app.apk
(cd mock-server && docker compose up -d mqtt)  # the mock broker
adb install -r tests/device/app.apk
php tests/device/run.php android              # prints one ":passed" or ":failed" line per scenario
```

`run.php` publishes through the broker with `docker exec mqtt php app/publish.php <topic> <payload>`.
