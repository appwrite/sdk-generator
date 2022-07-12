<?php

include __DIR__ . '/../../sdks/php/src/Appwrite/Client.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Service.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/InputFile.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/AppwriteException.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Services/Foo.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Services/Bar.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Services/General.php';

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\InputFile;
use Appwrite\Services\Bar;
use Appwrite\Services\Foo;
use Appwrite\Services\General;

$client = new Client();
$foo = new Foo($client, 'string');
$bar = new Bar($client);
$general = new General($client);

$client->addHeader('Origin', 'http://localhost');

echo "\nTest Started\n";

// Foo Service

$response = $foo->get(123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->post(123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->put(123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->patch(123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->delete(123, ['string in array']);
echo "{$response['result']}\n";

// Bar Service

$response = $bar->get('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $bar->post('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $bar->put('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $bar->patch('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $bar->delete('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $general->redirect();
echo "{$response['result']}\n";

$data = file_get_contents(__DIR__ . '/../../resources/file.png');
$response = $general->upload('string', 123, ['string in array'], InputFile::withData($data, 'image/png', 'file.png'));
echo "{$response['result']}\n";

$data = file_get_contents(__DIR__ . '/../../resources/large_file.mp4');
$response = $general->upload('string', 123, ['string in array'], InputFile::withData($data, 'video/mp4', 'large_file.mp4'));
echo "{$response['result']}\n";

$response = $general->upload('string', 123, ['string in array'], InputFile::withPath(__DIR__ .'/../../resources/file.png'));
echo "{$response['result']}\n";

$response = $general->upload('string', 123, ['string in array'], InputFile::withPath(__DIR__ .'/../../resources/large_file.mp4'));
echo "{$response['result']}\n";

try {
    $response = $general->error400();
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
}

try {
    $response = $general->error500();
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
}

try {
    $response = $general->error502();
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
}

$general->empty();
