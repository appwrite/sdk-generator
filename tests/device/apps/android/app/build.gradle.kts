plugins {
    id("com.android.application")
}

android {
    namespace = "io.appwrite.pushdevice"
    compileSdk = 37

    defaultConfig {
        applicationId = "io.appwrite.pushdevice.android"
        minSdk = 24
        targetSdk = 37
        versionCode = 1
        versionName = "1.0"
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
}

dependencies {
    implementation(project(":library"))
    implementation("androidx.appcompat:appcompat:1.8.0")
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.11.0")
}
