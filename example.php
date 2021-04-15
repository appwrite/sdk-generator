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
use Appwrite\SDK\Language\Java;
use Appwrite\SDK\Language\Typescript;
use Appwrite\SDK\Language\Deno;
use Appwrite\SDK\Language\HTTP;
use Appwrite\SDK\Language\Swift;
use Appwrite\SDK\Language\DotNet;
use Appwrite\SDK\Language\Flutter;

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
    
    $spec = file_get_contents('./specs/swagger-appwrite.0.7.0.json');

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

    // TypeScript
    $sdk  = new SDK(new Typescript(), new Swagger2($spec));

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

    $sdk->generate(__DIR__ . '/examples/typescript');

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

    // Java

    $sdk  = new SDK(new Java(), new Swagger2($spec));

    $sdk
        ->setName('NAME')
        ->setNamespace('io appwrite')
        ->setDescription('Repo description goes here')
        ->setShortDescription('Repo short description goes here')
        ->setURL('https://example.com')
        ->setGitUserName('appwrite')
        ->setGitRepoName('java-sdk')
        ->setLogo('https://appwrite.io/v1/images/console.png')
        ->setLicenseContent('test test test')
        ->setWarning('**WORK IN PROGRESS - NOT READY FOR USAGE**')
        ->setChangelog('**CHANGELOG**')
        ->setVersion('0.0.1')
        ->setTwitter('appwrite_io')
        ->setDiscord('564160730845151244', 'https://appwrite.io/discord')
        ->setDefaultHeaders([
            'X-Appwrite-Response-Format' => '0.7.0',
        ])
    ;

    $sdk->generate(__DIR__ . '/examples/java');

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

}
catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}

echo "Example SDKs generated successfully\n";
