// Runs the Flutter package's native Push plugin (android/: AppwritePushPlugin over the shared
// core) under Robolectric, the way the Android SDK's e2e runs its own, calling it over its
// method and event channels the way the SDK's Dart code does.
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
        maven("https://storage.googleapis.com/download.flutter.io")
    }
}

rootProject.name = "flutter-push-test"
