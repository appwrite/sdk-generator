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

rootProject.name = "push-device-android"
include(":app")
// The Android SDK generated into examples/android (php example.php android client).
include(":library")
project(":library").projectDir = file("../../../../examples/android/library")
