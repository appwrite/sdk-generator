<?php

namespace Appwrite\SDK\Language;


class Android extends Kotlin {

    /**
     * @return string
     */
    public function getName()
    {
        return 'Android';
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return [
            // Config for root project 
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => '/android/.github/workflows/publish.yml',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/kotlin/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/android/docs/kotlin/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/java/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/android/docs/java/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/wrapper/gradle-wrapper.jar',
                'template'      => 'android/gradle/wrapper/gradle-wrapper.jar',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/wrapper/gradle-wrapper.properties',
                'template'      => '/android/gradle/wrapper/gradle-wrapper.properties',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'scripts/publish-config.gradle',
                'template'      => '/android/scripts/publish-config.gradle',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'scripts/publish-module.gradle',
                'template'      => '/android/scripts/publish-module.gradle',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => '/android/.gitignore',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'build.gradle',
                'template'      => '/android/build.gradle.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/android/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle.properties',
                'template'      => '/android/gradle.properties',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradlew',
                'template'      => '/android/gradlew',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradlew.bat',
                'template'      => '/android/gradlew.bat',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => '/android/LICENSE.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/android/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'settings.gradle',
                'template'      => '/android/settings.gradle',
                'minify'        => false,
            ],
            // Config for project :library 
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Client.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/Client.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Query.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/Query.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/exceptions/{{spec.title | caseUcfirst}}Exception.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/exceptions/Exception.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/extensions/JsonExtensions.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/extensions/JsonExtensions.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/extensions/CollectionExtensions.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/extensions/CollectionExtensions.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/json/PreciseNumberAdapter.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/json/PreciseNumberAdapter.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/models/RealtimeModels.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/models/RealtimeModels.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/models/UploadProgress.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/models/UploadProgress.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/WebAuthComponent.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/WebAuthComponent.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/KeepAliveService.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/KeepAliveService.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/views/CallbackActivity.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/views/CallbackActivity.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/services/Service.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/services/Service.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/services/Realtime.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/services/Realtime.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/services/ServiceTemplate.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/AndroidManifest.xml',
                'template'      => '/android/library/src/main/AndroidManifest.xml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/build.gradle',
                'template'      => '/android/library/build.gradle.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/library/.gitignore',
                'template'      => '/android/library/.gitignore',
                'minify'        => false,
            ],
            // Config for project :example
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/MainActivity.kt',
                'template'      => '/android/example/src/main/java/io/appwrite/android/MainActivity.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/ui/accounts/AccountsFragment.kt',
                'template'      => '/android/example/src/main/java/io/appwrite/android/ui/accounts/AccountsFragment.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/ui/accounts/AccountsViewModel.kt',
                'template'      => '/android/example/src/main/java/io/appwrite/android/ui/accounts/AccountsViewModel.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/utils/Client.kt',
                'template'      => '/android/example/src/main/java/io/appwrite/android/utils/Client.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/utils/Event.kt',
                'template'      => '/android/example/src/main/java/io/appwrite/android/utils/Event.kt.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/drawable/ic_launcher_background.xml',
                'template'      => '/android/example/src/main/res/drawable/ic_launcher_background.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/drawable/ic_launcher_foreground.xml',
                'template'      => '/android/example/src/main/res/drawable/ic_launcher_foreground.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/layout/activity_main.xml',
                'template'      => '/android/example/src/main/res/layout/activity_main.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/layout/fragment_account.xml',
                'template'      => '/android/example/src/main/res/layout/fragment_account.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml',
                'template'      => '/android/example/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/mipmap-anydpi-v26/ic_launcher.xml',
                'template'      => '/android/example/src/main/res/mipmap-anydpi-v26/ic_launcher.xml'
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/values/colors.xml',
                'template'      => '/android/example/src/main/res/values/colors.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/values/strings.xml',
                'template'      => '/android/example/src/main/res/values/strings.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/res/values/themes.xml',
                'template'      => '/android/example/src/main/res/values/themes.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/src/main/AndroidManifest.xml',
                'template'      => '/android/example/src/main/AndroidManifest.xml',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/build.gradle',
                'template'      => '/android/example/build.gradle.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/.gitignore',
                'template'      => '/android/example/.gitignore',
                'minify'        => false,
            ],
            // Config for project :example-java
            [
                'scope'         => 'default',
                'destination'   => '/example-java/src/main/java/{{ sdk.namespace | caseSlash }}/example_java/MainActivity.java',
                'template'      => '/android/example-java/src/main/java/io/appwrite/example_java/MainActivity.java.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/drawable/ic_launcher_background.xml',
                'template'      => '/android/example-java/src/main/res/drawable/ic_launcher_background.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/drawable/ic_launcher_foreground.xml',
                'template'      => '/android/example-java/src/main/res/drawable/ic_launcher_foreground.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/layout/activity_main.xml',
                'template'      => '/android/example-java/src/main/res/layout/activity_main.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml',
                'template'      => '/android/example-java/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/mipmap-anydpi-v26/ic_launcher.xml',
                'template'      => '/android/example-java/src/main/res/mipmap-anydpi-v26/ic_launcher.xml'
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/values/colors.xml',
                'template'      => '/android/example-java/src/main/res/values/colors.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/values/strings.xml',
                'template'      => '/android/example-java/src/main/res/values/strings.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/res/values/themes.xml',
                'template'      => '/android/example-java/src/main/res/values/themes.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/src/main/AndroidManifest.xml',
                'template'      => '/android/example-java/src/main/AndroidManifest.xml',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example-java/build.gradle',
                'template'      => '/android/example-java/build.gradle.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example-java/.gitignore',
                'template'      => '/android/example-java/.gitignore',
                'minify'        => false,
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'library/src/main/java/io/appwrite/models/{{ definition.name | caseUcfirst }}.kt',
                'template'      => '/android/library/src/main/java/io/appwrite/models/Model.kt.twig',
                'minify'        => false,
            ],
        ];
    }
}

