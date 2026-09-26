plugins {
    id("com.android.library") version "9.4.1"
}

android {
    namespace = "io.appwrite.reactnative"
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
    implementation("com.hivemq:hivemq-mqtt-client:1.4.0")
    implementation("androidx.core:core-ktx:1.19.1")
    implementation("com.facebook.react:react-android:0.87.1")
    testImplementation("junit:junit:4.13.2")
    testImplementation("androidx.test.ext:junit-ktx:1.3.0")
    testImplementation("androidx.test:core-ktx:1.7.0")
    testImplementation("org.robolectric:robolectric:4.17")
}
