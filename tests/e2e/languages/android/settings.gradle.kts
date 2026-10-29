pluginManagement {
    val android = Regex("""id\("com\.android\.library"\) version "([^"]+)""")
        .find(settings.settingsDir.resolve("../../sdks/android/build.gradle.kts").readText())!!
        .groupValues[1]

    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }

    plugins {
        id("com.android.library") version android
    }
}

dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.PREFER_SETTINGS)
    repositories {
        google()
        mavenCentral()
    }
}

rootProject.name = "android"

include(":library")
project(":library").projectDir = settingsDir.resolve("../../sdks/android/library")
