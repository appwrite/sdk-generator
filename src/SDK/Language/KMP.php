<?php

namespace Appwrite\SDK\Language;

class KMP extends Kotlin
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'KMP';
    }

    protected function getReturnType(array $method, array $spec, string $namespace, bool $withGeneric = true, string $generic = 'T'): string
    {
        if ($method['type'] === 'webAuth') {
            return 'Bool';
        }
        return parent::getReturnType($method, $spec, $namespace, $withGeneric, $generic);
    }

    public function getFiles(): array
    {
        return [
            // Root project config
            [
                'scope'         => 'copy',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => '/kmp/.github/workflows/publish.yml',
            ],
//            [
//                'scope'         => 'method',
//                'destination'   => 'docs/examples/kotlin/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
//                'template'      => '/kmp/docs/kotlin/example.md.twig',
//            ],
//            [
//                'scope'         => 'method',
//                'destination'   => 'docs/examples/java/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
//                'template'      => '/kmp/docs/java/example.md.twig',
//            ],

            // Gradle files
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/wrapper/gradle-wrapper.jar',
                'template'      => '/kmp/gradle/wrapper/gradle-wrapper.jar',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/wrapper/gradle-wrapper.properties',
                'template'      => '/kmp/gradle/wrapper/gradle-wrapper.properties',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'gradle/libs.versions.toml',
                'template'      => '/kmp/gradle/libs.versions.toml',
            ],

            // Root files
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => '/kmp/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'build.gradle',
                'template'      => '/kmp/build.gradle.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'settings.gradle.kts',
                'template'      => '/kmp/settings.gradle.kts',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => '/kmp/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/kmp/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE.md',
                'template'      => '/kmp/LICENSE.md.twig',
            ],

            // Shared module
            [
                'scope'         => 'default',
                'destination'   => 'shared/build.gradle',
                'template'      => '/kmp/shared/build.gradle.twig',
            ],

            // Common Main
            // Common Main root files
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/BaseClient.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/BaseClient.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/Client.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/Client.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/ID.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/ID.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/Permission.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/Permission.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/Query.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/Query.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/Role.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/Role.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/Service.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/Service.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/WebAuthComponent.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/WebAuthComponent.kt.twig',
            ],

            // Coroutines
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/coroutines/Callback.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/coroutines/Callback.kt.twig',
            ],

            // Enums
            [
                'scope'         => 'enum',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/enums/{{ enum.name | caseUcfirst }}.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/enums/Enum.kt.twig',
            ],

            // Exceptions
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/exceptions/Exception.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/exceptions/Exception.kt.twig',
            ],

            // Extensions
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/CollectionExtensions.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/extensions/CollectionExtensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/JsonExtensions.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/extensions/JsonExtensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/TypeExtensions.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/extensions/TypeExtensions.kt.twig',
            ],

            // File Operations
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/fileOperations/FileOperations.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/fileOperations/FileOperations.kt.twig',
            ],

            // Models
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/models/Document.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/models/Document.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/models/InputFile.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/models/InputFile.kt.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/models//models/{{ definition.name | caseUcfirst }}.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/models/Model.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/models/RealtimeModels.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/models/RealtimeModels.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/models/UploadProgress.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/models/UploadProgress.kt.twig',
            ],

            // Serializers
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/serializers/DocumentSerializer.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/serializers/DocumentSerializer.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/serializers/DynamicLookupSerializer.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/serializers/DynamicLookupSerializer.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/serializers/StringCollectionSeriailizer.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/serializers/StringCollectionSeriailizer.kt.twig',
            ],

            // Services
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/services/Realtime.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/services/Realtime.kt.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/services/Service.kt.twig',
            ],

            // Web Interface
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/ParsedUrl.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/webInterface/ParsedUrl.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/UrlParser.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/webInterface/UrlParser.kt.twig',
            ],


            // Android Main
            // Android Main root files
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/AllCertsTrustManager.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/AllCertsTrustManager.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/Client.android.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/Client.android.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/HttpClientConfig.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/HttpClientConfig.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/KeepAliveService.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/KeepAliveService.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/WebAuthComponent.android.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/WebAuthComponent.android.kt.twig',
            ],

            // Cookies
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/cookies/AndroidCookieStorage.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/cookies/AndroidCookieStorage.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/cookies/Extensions.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/cookies/Extensions.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/cookies/InternalCookie.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/cookies/InternalCookie.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/cookies/stores/InMemoryCookieStore.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/cookies/stores/InMemoryCookieStore.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/cookies/stores/SharedPreferencesCookieStore.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/cookies/stores/SharedPreferencesCookieStore.kt.twig',
            ],

            // Extensions
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/createOAuth2Session.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/extensions/createOAuth2Session.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/createOAuth2Token.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/extensions/createOAuth2Token.kt.twig',
            ],

            // File Operations
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/fileOperations/FileOperations.android.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/fileOperations/FileOperations.android.kt.twig',
            ],

            // Models
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/models/InputFile.android.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/models/InputFile.android.kt.twig',
            ],

            // Views
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/views/CallbackActivity.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/views/CallbackActivity.kt.twig',
            ],

            // Web Interface
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/AndroidParsedUrl.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/webInterface/AndroidParsedUrl.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/androidMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/UrlParser.android.kt',
                'template'      => '/kmp/shared/src/androidMain/kotlin/io/package/webInterface/UrlParser.android.kt.twig',
            ],

            // iOS Main
            // iOS Main root files
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/Client.ios.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/Client.ios.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/HttpClientConfig.ios.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/HttpClientConfig.ios.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/WebAuthComponent.ios.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/WebAuthComponent.ios.kt.twig',
            ],

            // Cookies
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/cookies/IosCookieStorage.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/cookies/IosCookieStorage.kt.twig',
            ],

            // Extensions
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/createOAuth2Session.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/extensions/createOAuth2Session.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/createOAuth2Token.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/extensions/createOAuth2Token.kt.twig',
            ],

            // File Operations
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/fileOperations/FileOperations.ios.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/fileOperations/FileOperations.ios.kt.twig',
            ],

            // Models
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/models/InputFile.ios.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/models/InputFile.ios.kt.twig',
            ],

            // Web Interface
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/IosParsedUrl.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/webInterface/IosParsedUrl.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/iosMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/UrlParser.ios.kt',
                'template'      => '/kmp/shared/src/iosMain/kotlin/io/package/webInterface/UrlParser.ios.kt.twig',
            ],


            // JVM Main
            // JVM Main root files
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/AllCertsTrustManager.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/AllCertsTrustManager.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/Client.jvm.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/Client.jvm.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/HttpClient.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/HttpClient.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/WebAuthComponent.jvm.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/WebAuthComponent.jvm.kt.twig',
            ],

            // Extensions
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/createOAuth2Session.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/extensions/createOAuth2Session.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/extensions/createOAuth2Token.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/extensions/createOAuth2Token.kt.twig',
            ],

            // File Operations
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/fileOperations/FileOperations.jvm.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/fileOperations/FileOperations.jvm.kt.twig',
            ],

            // Models
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/models/InputFile.jvm.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/models/InputFile.jvm.kt.twig',
            ],

            // Web Interface
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/JvmParsedUrl.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/webInterface/JvmParsedUrl.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'shared/src/jvmMain/kotlin/{{ sdk.namespace | caseSlash }}/webInterface/UrlParser.jvm.kt',
                'template'      => '/kmp/shared/src/jvmMain/kotlin/io/package/webInterface/UrlParser.jvm.kt.twig',
            ],


            // Android App
            // Android App root files
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/AndroidManifest.xml',
                'template'      => '/kmp/androidApp/src/main/AndroidManifest.xml',
            ],

// Java files
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/java/{{ sdk.namespace | caseSlash }}/android/MainActivity.kt',
                'template'      => '/kmp/androidApp/src/main/java/io/package/android/MainActivity.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/java/{{ sdk.namespace | caseSlash }}/android/services/MessagingService.kt',
                'template'      => '/kmp/androidApp/src/main/java/io/package/android/services/MessagingService.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/java/{{ sdk.namespace | caseSlash }}/android/ui/accounts/AccountsFragment.kt',
                'template'      => '/kmp/androidApp/src/main/java/io/package/android/ui/accounts/AccountsFragment.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/java/{{ sdk.namespace | caseSlash }}/android/ui/accounts/AccountsViewModel.kt',
                'template'      => '/kmp/androidApp/src/main/java/io/package/android/ui/accounts/AccountsViewModel.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/java/{{ sdk.namespace | caseSlash }}/android/utils/Client.kt',
                'template'      => '/kmp/androidApp/src/main/java/io/package/android/utils/Client.kt.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'androidApp/src/main/java/{{ sdk.namespace | caseSlash }}/android/utils/Event.kt',
                'template'      => '/kmp/androidApp/src/main/java/io/package/android/utils/Event.kt.twig',
            ],

// Resource files
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/drawable/ic_launcher_background.xml',
                'template'      => '/kmp/androidApp/src/main/res/drawable/ic_launcher_background.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/drawable/ic_launcher_foreground.xml',
                'template'      => '/kmp/androidApp/src/main/res/drawable/ic_launcher_foreground.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/layout/activity_main.xml',
                'template'      => '/kmp/androidApp/src/main/res/layout/activity_main.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/layout/fragment_account.xml',
                'template'      => '/kmp/androidApp/src/main/res/layout/fragment_account.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/mipmap-anydpi-v26/ic_launcher.xml',
                'template'      => '/kmp/androidApp/src/main/res/mipmap-anydpi-v26/ic_launcher.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml',
                'template'      => '/kmp/androidApp/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/values/colors.xml',
                'template'      => '/kmp/androidApp/src/main/res/values/colors.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/values/strings.xml',
                'template'      => '/kmp/androidApp/src/main/res/values/strings.xml',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'androidApp/src/main/res/values/themes.xml',
                'template'      => '/kmp/androidApp/src/main/res/values/themes.xml',
            ],


            // Models, Services, and other common components
            [
                'scope'         => 'service',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/services/{{service.name | caseUcfirst}}.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/services/Service.kt.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/models/{{ definition.name | caseUcfirst }}.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/models/Model.kt.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'shared/src/commonMain/kotlin/{{ sdk.namespace | caseSlash }}/enums/{{ enum.name | caseUcfirst }}.kt',
                'template'      => '/kmp/shared/src/commonMain/kotlin/io/package/enums/Enum.kt.twig',
            ],
        ];
    }
}
