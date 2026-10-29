pluginManagement {
    val kotlin = Regex("""kotlin\("jvm"\) version "([^"]+)""")
        .find(settings.settingsDir.resolve("../../sdks/kotlin/build.gradle.kts").readText())!!
        .groupValues[1]

    plugins {
        id("org.jetbrains.kotlin.jvm") version kotlin
    }
}

rootProject.name = "kotlin"

includeBuild("../../sdks/kotlin") {
    name = "sdk"
    dependencySubstitution {
        substitute(module("io.appwrite:reponame")).using(project(":"))
    }
}
