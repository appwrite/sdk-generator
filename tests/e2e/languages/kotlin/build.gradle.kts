plugins {
    id("org.jetbrains.kotlin.jvm")
}

repositories {
    mavenCentral()
}

dependencies {
    testImplementation("io.appwrite:reponame")
    testImplementation("com.google.code.gson:gson:2.15.0")
    testImplementation(kotlin("test-junit"))
}

sourceSets {
    test {
        java.srcDir("build/examples")
    }
}

tasks.test {
    useJUnit()
}
