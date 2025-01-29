<?php

namespace Appwrite\SDK\Language;

class Android extends Kotlin
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Android';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            // Config for root project
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => '/android/.github/workflows/publish.yml',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/kotlin/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/android/docs/kotlin/example.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/java/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/android/docs/java/example.md.twig',
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
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'scripts/publish-config.gradle',
                'template'      => '/android/scripts/publish-config.gradle',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'scripts/publish-module.gradle',
                'template'      => '/android/scripts/publish-module.gradle',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => '/android/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'build.gradle',
                'template'      => '/android/build.gradle.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/android/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle.properties',
                'template'      => '/android/gradle.properties',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradlew',
                'template'      => '/android/gradlew',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradlew.bat',
                'template'      => '/android/gradlew.bat',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => '/android/LICENSE.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/android/README.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'settings.gradle',
                'template'      => '/android/settings.gradle',
            ],
            // Config for project :library
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Client.kt',
                'template'      => '/android/library/src/main/java/io/package/Client.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Permission.kt',
                'template'      => '/android/library/src/main/java/io/package/Permission.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Role.kt',
                'template'      => '/android/library/src/main/java/io/package/Role.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/ID.kt',
                'template'      => '/android/library/src/main/java/io/package/ID.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Query.kt',
                'template'      => '/android/library/src/main/java/io/package/Query.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/exceptions/{{spec.title | caseUcfirst}}Exception.kt',
                'template'      => '/android/library/src/main/java/io/package/exceptions/Exception.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/extensions/JsonExtensions.kt',
                'template'      => '/android/library/src/main/java/io/package/extensions/JsonExtensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/extensions/TypeExtensions.kt',
                'template'      => '/android/library/src/main/java/io/package/extensions/TypeExtensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/extensions/CollectionExtensions.kt',
                'template'      => '/android/library/src/main/java/io/package/extensions/CollectionExtensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/models/InputFile.kt',
                'template'      => '/android/library/src/main/java/io/package/models/InputFile.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/models/RealtimeModels.kt',
                'template'      => '/android/library/src/main/java/io/package/models/RealtimeModels.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/models/UploadProgress.kt',
                'template'      => '/android/library/src/main/java/io/package/models/UploadProgress.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/WebAuthComponent.kt',
                'template'      => '/android/library/src/main/java/io/package/WebAuthComponent.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/KeepAliveService.kt',
                'template'      => '/android/library/src/main/java/io/package/KeepAliveService.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/views/CallbackActivity.kt',
                'template'      => '/android/library/src/main/java/io/package/views/CallbackActivity.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/Service.kt',
                'template'      => '/android/library/src/main/java/io/package/Service.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/services/Realtime.kt',
                'template'      => '/android/library/src/main/java/io/package/services/Realtime.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/cookies/Extensions.kt',
                'template'      => '/android/library/src/main/java/io/package/cookies/Extensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/coroutines/Callback.kt',
                'template'      => '/android/library/src/main/java/io/package/coroutines/Callback.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/cookies/stores/InMemoryCookieStore.kt',
                'template'      => '/android/library/src/main/java/io/package/cookies/stores/InMemoryCookieStore.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/cookies/stores/SharedPreferencesCookieStore.kt',
                'template'      => '/android/library/src/main/java/io/package/cookies/stores/SharedPreferencesCookieStore.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/cookies/InternalCookie.kt',
                'template'      => '/android/library/src/main/java/io/package/cookies/InternalCookie.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/cookies/ListenableCookieJar.kt',
                'template'      => '/android/library/src/main/java/io/package/cookies/ListenableCookieJar.kt.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/library/src/main/java/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.kt',
                'template'      => '/android/library/src/main/java/io/package/services/Service.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/src/main/AndroidManifest.xml',
                'template'      => '/android/library/src/main/AndroidManifest.xml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/library/build.gradle',
                'template'      => '/android/library/build.gradle.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/library/.gitignore',
                'template'      => '/android/library/.gitignore',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'library/src/main/java/{{ sdk.namespace | caseSlash }}/models/{{ definition.name | caseUcfirst }}.kt',
                'template'      => '/android/library/src/main/java/io/package/models/Model.kt.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'library/src/main/java/{{ sdk.namespace | caseSlash }}/enums/{{ enum.name | caseUcfirst }}.kt',
                'template'      => '/android/library/src/main/java/io/package/enums/Enum.kt.twig',
            ],
            // Config for project :example
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/MainActivity.kt',
                'template'      => '/android/example/src/main/java/io/package/android/MainActivity.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/ui/accounts/AccountsFragment.kt',
                'template'      => '/android/example/src/main/java/io/package/android/ui/accounts/AccountsFragment.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/ui/accounts/AccountsViewModel.kt',
                'template'      => '/android/example/src/main/java/io/package/android/ui/accounts/AccountsViewModel.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/utils/Client.kt',
                'template'      => '/android/example/src/main/java/io/package/android/utils/Client.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/utils/Event.kt',
                'template'      => '/android/example/src/main/java/io/package/android/utils/Event.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/src/main/java/{{ sdk.namespace | caseSlash }}/android/services/MessagingService.kt',
                'template'      => '/android/example/src/main/java/io/package/android/services/MessagingService.kt.twig',
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
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/build.gradle',
                'template'      => '/android/example/build.gradle.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/example/.gitignore',
                'template'      => '/android/example/.gitignore',
            ],
        ];
    }

    protected function getReturnType(array $method, array $spec, string $namespace, string $generic = 'T', bool $withGeneric = true): string
    {
        if ($method['type'] === 'webAuth') {
            return 'Bool';
        }
        return parent::getReturnType($method, $spec, $namespace, $generic, $withGeneric);
    }
}
