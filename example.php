<?php

include_once 'vendor/autoload.php';

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;
use Appwrite\SDK\Language\JS;
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

$languages  = ['js', 'node', 'php', 'python', 'ruby', 'dart', 'go', 'java', 'swift', 'typescript', 'deno', 'http'];

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
    $spec = getSSLPage('https://appwrite.io/v1/open-api-2.json?extensions=1'); // Enable only with Appwrite local server running on port 80
    
    if(empty($spec)) {
        throw new Exception('Failed to fetch spec from Appwrite server');
    }

    // PHP
    $sdk  = new SDK(new PHP(), new Swagger2($spec));

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
    ;

    $sdk->generate(__DIR__ . '/examples/php');

    // JS
    $sdk  = new SDK(new JS(), new Swagger2($spec));

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
    ;

    $sdk->generate(__DIR__ . '/examples/js');

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
    ;

    $sdk->generate(__DIR__ . '/examples/python');

    // Dart
    $sdk  = new SDK(new Dart(), new Swagger2($spec));

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
    ;

    $sdk->generate(__DIR__ . '/examples/dart');

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
    ;

    $sdk->generate(__DIR__ . '/examples/swift');

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
    ;

    $sdk->generate(__DIR__ . '/examples/http');
}
catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}

echo "Example SDKs generated successfully\n";
