<?php

include_once 'vendor/autoload.php';

use Appwrite\SDK\Language\CLI;
use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\Web;
use Appwrite\SDK\Language\Node;
use Appwrite\SDK\Language\PHP;
use Appwrite\SDK\Language\Python;
use Appwrite\SDK\Language\Ruby;
use Appwrite\SDK\Language\Dart;
use Appwrite\SDK\Language\Go;
use Appwrite\SDK\Language\Deno;
use Appwrite\SDK\Language\HTTP;
use Appwrite\SDK\Language\Swift;
use Appwrite\SDK\Language\DotNet;
use Appwrite\SDK\Language\Flutter;
use Appwrite\SDK\Language\Kotlin;

try {

    function getSSLPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //$spec = getSSLPage('https://appwrite.io/v1/open-api-2.json?extensions=1');
    // $spec = getSSLPage('https://appwrite.io/v1/open-api-2.json?extensions=1'); // Enable only with Appwrite local server running on port 80
    // $spec = getSSLPage('https://appwrite.io/v1/open-api-2.json?extensions=1&platform=console'); // Enable only with Appwrite local server running on port 80
    // $spec = file_get_contents('https://appwrite.io/specs/swagger2?platform=client');
    $spec = file_get_contents('./specs/swagger-appwrite.0.8.0.json');

    if(empty($spec)) {
        throw new Exception('Failed to fetch spec from Appwrite server');
    }

    // PHP
    $php = new PHP();
    $php
        ->setComposerVendor('appwrite')
        ->setComposerPackage('appwrite');
    $sdk  = new SDK($php, new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/images/github.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/php');

    // Web
    $sdk  = new SDK(new Web(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setVersion('0.0.0')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setReadme("## Getting Started")
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/web');

    // Deno
    $sdk  = new SDK(new Deno(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setVersion('0.0.0')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/deno');

    // Node
    $sdk  = new SDK(new Node(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/node');

    // Ruby
    $sdk  = new SDK(new Ruby(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/ruby');

    // Python
    $sdk  = new SDK(new Python(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/python');

    // Dart
    $dart = new Dart();
    $dart->setPackageName('dart_appwrite');

    $sdk  = new SDK($dart, new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setExamples('**EXAMPLES** <HTML>')
        ->setVersion('0.0.1')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/dart');

    // Flutter
    $flutter = new Flutter();
    $flutter->setPackageName('appwrite');
    $sdk  = new SDK($flutter, new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setExamples('**EXAMPLES** <HTML>')
        ->setVersion('0.0.1')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/flutter');

    // GO

    $sdk  = new SDK(new Go(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setGitUserName('appwrite')
        ->setGitRepoName('go-sdk')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/go');


    // Swift
    $sdk  = new SDK(new Swift(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setVersion('0.0.1')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/swift');
    
    // DotNet
    $sdk  = new SDK(new DotNet(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setVersion('0.0.1')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/dotnet');

    // HTTP
    $sdk  = new SDK(new HTTP(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setVersion('0.0.1')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
    ;

    $sdk->generate(__DIR__ . '/examples/HTTP');

    // CLI
    $cli = new CLI();
    $cli->setExecutableName('appwrite');
    $cli->setLogo("
    _                            _ _           ___   __   _____ 
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
       |_|   |_|                                                  
 ");
    $sdk  = new SDK($cli, new Swagger2($spec));
    $sdk
        ->setName('NAME')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setVersion('0.0.1')
        ->setGitUserName('repoowner')
        ->setGitRepoName('reponame')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        // ->setDefaultHeaders([
        //     'X-Appwrite-Response-Format' => '0.7.0',
        // ])
    ;
    $sdk->generate(__DIR__ . '/examples/CLI');

    // Kotlin

    $sdk = new SDK(new Kotlin(), new Swagger2($spec));
    
    $sdk
        ->setName('Kotlin')
        ->setNamespace('io appwrite')
        ->setDescription('Appwrite is an open-source backend as a service server that abstract and simplify complex and repetitive development tasks behind a very simple to use REST API. Appwrite aims to help you develop your apps faster and in a more secure way. Use the Flutter SDK to integrate your app with the Appwrite server to easily start interacting with all of Appwrite backend APIs and tools. For full API documentation and tutorials go to https://appwrite.io/docs')
        ->setShortDescription('Appwrite Kotlin SDK')
        ->setURL('https://example.com')
        ->setGitUserName('appwrite')
        ->setGitRepoName('sdk-for-android')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**This SDK is compatible with Appwrite server version 0.7.x. For older versions, please check previous releases.**')
        ->setGettingStarted("
### Add your Android Platform
To init your SDK and start interacting with Appwrite services, you need to add a new Flutter platform to your project. To add a new platform, go to your Appwrite console, choose the project you created in the step before, and click the 'Add Platform' button.

From the options, choose to add a new **Flutter** platform and add your app credentials, ignoring iOS.

Add your app <u>name</u> and <u>package name</u>, Your package name is generally the applicationId in your app-level build.gradle file. By registering your new app platform, you are allowing your app to communicate with the Appwrite API.

### OAuth
In order to capture the Appwrite OAuth callback url, the following activity needs to be added to your [AndroidManifest.xml](https://github.com/appwrite/playground-for-flutter/blob/master/android/app/src/main/AndroidManifest.xml). Be sure to relpace the **[PROJECT_ID]** string with your actual Appwrite project ID. You can find your Appwrite project ID in you project settings screen in your Appwrite console.

```xml
<manifest>
    <application>
        <activity android:name=\"io.appwrite.views.CallbackActivity\" >
            <intent-filter android:label=\"android_web_auth\">
                <action android:name=\"android.intent.action.VIEW\" />
                <category android:name=\"android.intent.category.DEFAULT\" />
                <category android:name=\"android.intent.category.BROWSABLE\" />
                <data android:scheme=\"appwrite-callback-[PROJECT_ID]\" />
            </intent-filter>
        </activity>
    </application>
</manifest>
```

### Init your SDK

<p>Initialize your SDK code with your project ID, which can be found in your project settings page.

```kotlin
import io.appwrite.Client
import io.appwrite.services.Account

val client = Client(context)
  .setEndpoint(\"https://[HOSTNAME_OR_IP]/v1\") // Your API Endpoint
  .setProject(\"5df5acd0d48c2\") // Your project ID
  .setSelfSigned(true) // Remove in production
```

Before starting to send any API calls to your new Appwrite instance, make sure your Android emulators has network access to the Appwrite server hostname or IP address.

When trying to connect to Appwrite from an emulator or a mobile device, localhost is the hostname for the device or emulator and not your local Appwrite instance. You should replace localhost with your private IP as the Appwrite endpoint's hostname. You can also use a service like [ngrok](https://ngrok.com/) to proxy the Appwrite API.

### Make Your First Request

<p>Once your SDK object is set, access any of the Appwrite services and choose any request to send. Full documentation for any service method you would like to use can be found in your SDK documentation or in the API References section.

```kotlin
// Register User
val accountService = Account(client)
val user = accountService.create(
    \"email@example.com\", 
    \"password\"
)
```

### Full Example

```kotlin
import io.appwrite.Client
import io.appwrite.services.Account

val client = Client(context)
  .setEndpoint(\"https://[HOSTNAME_OR_IP]/v1\") // Your API Endpoint
  .setProject(\"5df5acd0d48c2\") // Your project ID
  .setSelfSigned(true) // Remove in production

val accountService = Account(client)
val user = accountService.create(
    \"email@example.com\", 
    \"password\"
)
```

### Learn more
You can use following resources to learn more and get help
- 📜 [Appwrite Docs](https://appwrite.io/docs)
- 💬 [Discord Community](https://appwrite.io/discord)
        ")
        ->setChangelog('**CHANGELOG**')
        ->setVersion('0.0.0-SNAPSHOT')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'x-appwrite-response-format' => '0.7.0',
        ])
    ;
    $sdk->generate(__DIR__ . '/examples/kotlin-android');
}
catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}

echo "Example SDKs generated successfully\n";
