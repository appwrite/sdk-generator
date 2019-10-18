<?php

include_once realpath(__DIR__ . '/../../vendor/autoload.php');

use Appwrite\Spec\Swagger2;
use Appwrite\SDK\SDK;

$languages  = [
    'php' => [
        'class' => 'Appwrite\SDK\Language\PHP',
    ],
    'node' => [
        'class' => 'Appwrite\SDK\Language\Node',
    ],
];

try {
    $spec = file_get_contents(__DIR__ . '/spec.json');

    if(empty($spec)) {
        throw new Exception('Failed to fetch spec from Appwrite server');
    }

    foreach ($languages as $language => $options) {
        $sdk  = new SDK(new $options['class'](), new Swagger2($spec));

        $sdk
            ->setLicenseContent('demo license')
        ;

        $sdk->generate(__DIR__ . '/../sdks/' . $language);
    }
}
catch (Exception $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}
catch (Throwable $exception) {
    echo 'Error: ' . $exception->getMessage() . ' on ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
}

echo 'Example SDKs generated successfully';