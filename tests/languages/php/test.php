<?php

include __DIR__ . '/../sdks/php/src/Appwrite/Client.php';
include __DIR__ . '/../sdks/php/src/Appwrite/Service.php';
include __DIR__ . '/../sdks/php/src/Appwrite/Services/Foo.php';
include __DIR__ . '/../sdks/php/src/Appwrite/Services/Bar.php';
include __DIR__ . '/../sdks/php/src/Appwrite/Services/General.php';

use Appwrite\Client;
use Appwrite\Services\Foo;
use Appwrite\Services\Bar;
use Appwrite\Services\General;

$client     = new Client();
$foo        = new Foo($client);
$bar        = new Bar($client);
$general    = new General($client);

$client->addHeader('Origin', 'http://localhost');

// Foo Service

$resposne = $foo->get('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $foo->post('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $foo->put('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $foo->patch('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $foo->delete('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

// Bar Service

$resposne = $bar->get('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $bar->post('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $bar->put('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $bar->patch('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $bar->delete('string', 123, ['string in array']);
echo "{$resposne['result']}\n";

$resposne = $general->redirect();
echo "{$resposne['result']}\n";
