plugins {
    id("com.android.library") version "9.5.0"
}

val module = "../../../sdks/react-native/android/src/main"
val result = layout.projectDirectory.file("result.txt").asFile

android {
    namespace = "io.appwrite.reactnative"
    compileSdk = 37

    defaultConfig {
        minSdk = 24
    }

    sourceSets {
        getByName("main") {
            java.srcDir("$module/java")
            kotlin.srcDir("$module/java")
            manifest.srcFile("$module/AndroidManifest.xml")
        }
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

tasks.withType<Test>().configureEach {
    outputs.upToDateWhen { false }
    doFirst { result.delete() }
}

dependencies {
    implementation("com.hivemq:hivemq-mqtt-client:1.3.6")
    implementation("androidx.core:core-ktx:1.13.1")
    implementation("com.facebook.react:react-android:0.88.1")
    testImplementation("junit:junit:4.13.2")
    testImplementation("androidx.test.ext:junit-ktx:1.3.0")
    testImplementation("androidx.test:core-ktx:1.7.0")
    testImplementation("org.robolectric:robolectric:4.17")
}
