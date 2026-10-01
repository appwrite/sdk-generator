
afterEvaluate {
    val android = extensions.getByType(com.android.build.api.dsl.LibraryExtension::class.java)
    val components = extensions.getByType(com.android.build.api.variant.LibraryAndroidComponentsExtension::class.java)
    val r8Directory = layout.buildDirectory.dir("r8")
    val r8Program = r8Directory.map { it.file("program.jar") }
    val r8Output = r8Directory.map { it.file("classes.jar") }

    fun artifacts(configuration: String, type: String) = configurations.getByName(configuration).incoming.artifactView {
        attributes {
            attribute(Attribute.of("artifactType", String::class.java), type)
        }
    }

    fun moduleOf(id: Any?) = (id as? org.gradle.api.artifacts.component.ModuleComponentIdentifier)?.let { "${it.group}:${it.module}" }

    val testModules = provider {
        val roots = configurations.getByName("testImplementation").dependencies.map { "${it.group}:${it.name}" }.toSet()
        val queue = configurations.getByName("debugUnitTestRuntimeClasspath").incoming.resolutionResult.root.dependencies
            .filterIsInstance<org.gradle.api.artifacts.result.ResolvedDependencyResult>()
            .map { it.selected }
            .filter { moduleOf(it.id) in roots }
            .toMutableList()
        val seen = mutableSetOf<String>()
        while (queue.isNotEmpty()) {
            val component = queue.removeAt(queue.lastIndex)
            if (seen.add(moduleOf(component.id) ?: continue)) {
                queue += component.dependencies.filterIsInstance<org.gradle.api.artifacts.result.ResolvedDependencyResult>().map { it.selected }
            }
        }
        seen
    }

    val runtimeDependencies = artifacts("debugRuntimeClasspath", "android-classes-jar")
    val programDependencies = files(provider { runtimeDependencies.artifacts.filter { moduleOf(it.id.componentIdentifier) !in testModules.get() }.map { it.file } })
    val testArtifacts = artifacts("debugUnitTestRuntimeClasspath", "android-classes-jar")
    val testDependencies = files(provider {
        testArtifacts.artifacts
            .filterNot { it.id.componentIdentifier is org.gradle.api.artifacts.component.ProjectComponentIdentifier }
            .map { it.file }
    })
    val dependencyRules = artifacts("debugRuntimeClasspath", "android-consumer-proguard-rules").files
    val libraryDependencies = testDependencies.filter { it !in programDependencies.files }

    val packageR8Program = tasks.register<Jar>("packageR8Program") {
        dependsOn("compileDebugKotlin", "compileDebugJavaWithJavac", "compileDebugUnitTestKotlin", "compileDebugUnitTestJavaWithJavac")
        destinationDirectory.set(r8Directory)
        archiveFileName.set("program.jar")
        duplicatesStrategy = DuplicatesStrategy.EXCLUDE
        from(layout.buildDirectory.dir("intermediates/built_in_kotlinc/debug/compileDebugKotlin/classes"))
        from(layout.buildDirectory.dir("intermediates/javac/debug/compileDebugJavaWithJavac/classes"))
        from(layout.buildDirectory.dir("intermediates/built_in_kotlinc/debugUnitTest/compileDebugUnitTestKotlin/classes"))
        from(layout.buildDirectory.dir("intermediates/javac/debugUnitTest/compileDebugUnitTestJavaWithJavac/classes"))
    }

    val shrinkWithR8 = tasks.register<JavaExec>("shrinkWithR8") {
        dependsOn(packageR8Program, "extractProguardFiles")
        inputs.file(r8Program)
        inputs.files(programDependencies, libraryDependencies, dependencyRules)
        inputs.files("consumer-rules.pro", "r8-rules.pro", "src/main/AndroidManifest.xml")
        outputs.file(r8Output)
        classpath(files(Class.forName("com.android.tools.r8.R8").protectionDomain.codeSource.location))
        mainClass.set("com.android.tools.r8.R8")

        doFirst {
            val manifestRules = r8Directory.get().file("manifest-rules.pro").asFile.apply {
                writeText(
                    Regex("android:name=\"([\\w.]+)\"")
                        .findAll(file("src/main/AndroidManifest.xml").readText())
                        .map { it.groupValues[1] }
                        .filter { !it.startsWith("android.") }
                        .joinToString("\n") { "-keep class $it { <init>(); }" },
                )
            }
            val rules = listOf(
                android.getDefaultProguardFile("proguard-android-optimize.txt"),
                file("consumer-rules.pro"),
                file("r8-rules.pro"),
                manifestRules,
            ) + dependencyRules.flatMap { root ->
                val files = root.walkTopDown().filter { it.isFile }.toList()
                val r8 = files.filter { "com.android.tools/r8" in it.invariantSeparatorsPath && "-upto-" !in it.path }
                r8.ifEmpty { files.filter { "com.android.tools" !in it.invariantSeparatorsPath } }
            }

            args = buildList {
                addAll(listOf("--classfile", "--release", "--output", r8Output.get().asFile.absolutePath))
                addAll(listOf("--pg-map-output", r8Directory.get().file("mapping.txt").asFile.absolutePath))
                components.sdkComponents.bootClasspath.get().forEach { addAll(listOf("--lib", it.asFile.absolutePath)) }
                libraryDependencies.forEach { addAll(listOf("--lib", it.absolutePath)) }
                rules.forEach { addAll(listOf("--pg-conf", it.absolutePath)) }
                add(r8Program.get().asFile.absolutePath)
                programDependencies.forEach { add(it.absolutePath) }
            }
        }
    }

    val extractR8Tests = tasks.register<Sync>("extractR8Tests") {
        dependsOn(shrinkWithR8)
        from(r8Output.map { zipTree(it) })
        into(r8Directory.map { it.dir("tests") })
        include("io/appwrite/ServiceTest*.class")
    }

    tasks.register<Test>("testR8UnitTest") {
        dependsOn(extractR8Tests)
        testClassesDirs = files(extractR8Tests)
        classpath = files(r8Output) + libraryDependencies + files(components.sdkComponents.bootClasspath)
        systemProperties(tasks.getByName<Test>("testDebugUnitTest").systemProperties)
        workingDir = projectDir
        useJUnit()
        testLogging {
            exceptionFormat = org.gradle.api.tasks.testing.logging.TestExceptionFormat.FULL
            events("failed")
        }
    }
}
