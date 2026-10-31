plugins {
    id("com.android.library") version "9.5.0"
}

val plugin = file("../../../sdks/flutter/android")

android {
    namespace = "io.appwrite.flutter"
    compileSdk = 37

    defaultConfig {
        minSdk = 24
    }

    testOptions {
        unitTests.all {
            it.systemProperty("robolectric.conscryptMode", "OFF")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    sourceSets {
        getByName("main") {
            kotlin.srcDir(plugin.resolve("src/main/kotlin"))
            manifest.srcFile(plugin.resolve("src/main/AndroidManifest.xml"))
        }
    }
}

// Build what a consumer builds: the plugin's own dependencies, read from the generated package's
// android/build.gradle, and the Flutter embedding of the Flutter SDK this e2e runs with
// (flutter --version --machine, written to flutter-version.json).
val pluginDependencies = Regex("""implementation\("([^"]+)"\)""")
    .findAll(plugin.resolve("build.gradle").readText())
    .map { it.groupValues[1] }
    .toList()
val engineRevision = Regex(""""engineRevision"\s*:\s*"([0-9a-f]+)"""")
    .find(file("flutter-version.json").readText())!!
    .groupValues[1]

dependencies {
    pluginDependencies.forEach { implementation(it) }
    implementation("io.flutter:flutter_embedding_debug:1.0.0-$engineRevision")
    testImplementation("junit:junit:4.13.2")
    testImplementation("androidx.test.ext:junit-ktx:1.3.0")
    testImplementation("androidx.test:core-ktx:1.7.0")
    testImplementation("org.robolectric:robolectric:4.17")
}
