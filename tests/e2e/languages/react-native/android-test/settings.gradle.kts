// Runs the React Native package's native Push module (android/: AppwritePushModule over the
// shared core) under Robolectric, the way the Android SDK's e2e runs its own, calling it the way
// the SDK's push.ts does.
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}

dependencyResolutionManagement {
    repositories {
        google()
        mavenCentral()
    }
}

rootProject.name = "react-native-push-test"
