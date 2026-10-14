#!/usr/bin/env bash
# Generate an SDK from the test spec and build its Push device test app into tests/device/app.apk.
#
#     tests/device/build.sh <android|react-native|flutter>
set -euo pipefail

sdk="${1:?usage: tests/device/build.sh <android|react-native|flutter>}"
root="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$root"
export SDK_GEN_SPEC_FILE="$root/tests/resources/spec-openapi3.json"

case "$sdk" in
  android)
    php example.php android client
    (cd tests/device/apps/android && ./gradlew --no-daemon :app:assembleDebug)
    apk=tests/device/apps/android/app/build/outputs/apk/debug/app-debug.apk
    ;;
  react-native)
    php example.php react-native client
    (cd examples/react-native && npm ci --omit=peer && npm run build && npm pack && mv react-native-appwrite-*.tgz react-native-appwrite.tgz)
    (cd tests/device/apps/react-native \
      && npm install \
      && npx expo prebuild --platform android --no-install \
      && cd android && ./gradlew --no-daemon :app:assembleRelease)
    apk=tests/device/apps/react-native/android/app/build/outputs/apk/release/app-release.apk
    ;;
  flutter)
    php example.php flutter client
    (cd tests/device/apps/flutter && flutter pub get && flutter build apk --release)
    apk=tests/device/apps/flutter/build/app/outputs/flutter-apk/app-release.apk
    ;;
  *)
    echo "unknown SDK: $sdk" >&2
    exit 2
    ;;
esac

cp "$apk" tests/device/app.apk
