import java.util.Properties

plugins {
    id("io.github.gradle-nexus.publish-plugin") version "2.0.0"
    id("com.android.library") version "9.1.1" apply false
    id("com.android.application") version "9.1.1" apply false
}

version = System.getenv("SDK_VERSION") ?: "0.0.0"

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}

val signingKeyId: String = System.getenv("SIGNING_KEY_ID") ?: ""
val signingPassword: String = System.getenv("SIGNING_PASSWORD") ?: ""
val signingSecretKeyRingFile: String = System.getenv("SIGNING_SECRET_KEY_RING_FILE") ?: ""
val ossrhUsername: String = System.getenv("OSSRH_USERNAME") ?: ""
val ossrhPassword: String = System.getenv("OSSRH_PASSWORD") ?: ""
val sonatypeStagingProfileId: String = System.getenv("SONATYPE_STAGING_PROFILE_ID") ?: ""

val secretPropsFile = project.rootProject.file("local.properties")
val localProperties = Properties().apply {
    if (secretPropsFile.exists()) {
        secretPropsFile.inputStream().use { load(it) }
    }
}

extra["signing.keyId"] = signingKeyId.ifEmpty { localProperties.getProperty("signing.keyId", "") }
extra["signing.password"] = signingPassword.ifEmpty { localProperties.getProperty("signing.password", "") }
extra["signing.secretKeyRingFile"] = signingSecretKeyRingFile.ifEmpty { localProperties.getProperty("signing.secretKeyRingFile", "") }

nexusPublishing {
    repositories {
        sonatype {
            stagingProfileId.set(sonatypeStagingProfileId.ifEmpty { localProperties.getProperty("sonatypeStagingProfileId", "") })
            username.set(ossrhUsername.ifEmpty { localProperties.getProperty("ossrhUsername", "") })
            password.set(ossrhPassword.ifEmpty { localProperties.getProperty("ossrhPassword", "") })
            nexusUrl.set(uri("https://ossrh-staging-api.central.sonatype.com/service/local/"))
            snapshotRepositoryUrl.set(uri("https://central.sonatype.com/repository/maven-snapshots/"))
        }
    }
}
