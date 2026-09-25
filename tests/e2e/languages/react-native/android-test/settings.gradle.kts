// Runs the React Native package's native Push module (android/: the shared core and PushBridge)
// under Robolectric, the way the Android SDK's e2e runs its own. The React Native adapter
// (reactnative/) needs a full React Native build, so only the core and the bridge are compiled.
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
