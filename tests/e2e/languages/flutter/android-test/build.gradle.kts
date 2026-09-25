plugins {
    id("com.android.library") version "9.4.0"
}

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
}

dependencies {
    implementation("com.hivemq:hivemq-mqtt-client:1.3.6")
    implementation("androidx.core:core-ktx:1.13.1")
    // The Flutter embedding (plugins, channels, codecs) of Flutter 3.35.7.
    implementation("io.flutter:flutter_embedding_debug:1.0.0-035316565ad77281a75305515e4682e6c4c6f7ca")
    testImplementation("junit:junit:4.13.2")
    testImplementation("androidx.test.ext:junit-ktx:1.3.0")
    testImplementation("androidx.test:core-ktx:1.7.0")
    testImplementation("org.robolectric:robolectric:4.17")
}
