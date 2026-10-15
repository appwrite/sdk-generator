#!/bin/sh
# Runs the Flutter web e2e (web_tests.dart) under a headless browser.
#
# The Flutter image ships no browser, so install Google Chrome, then run the test with
# `flutter test --platform chrome`. Chrome runs as root inside the container, so it is
# wrapped with --no-sandbox (root cannot use the sandbox) and --disable-dev-shm-usage
# (containers have a tiny /dev/shm, which otherwise crashes Chrome with SIGABRT).
set -e

apt-get update -qq
apt-get install -y -qq wget gnupg >/dev/null
wget -q https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
apt-get install -y -qq ./google-chrome-stable_current_amd64.deb >/dev/null

cat > /usr/local/bin/chrome-nosandbox <<'WRAP'
#!/bin/sh
exec /usr/bin/google-chrome --no-sandbox --disable-dev-shm-usage "$@"
WRAP
chmod +x /usr/local/bin/chrome-nosandbox
export CHROME_EXECUTABLE=/usr/local/bin/chrome-nosandbox

flutter pub get
flutter test --platform chrome test/appwrite_web_test.dart
