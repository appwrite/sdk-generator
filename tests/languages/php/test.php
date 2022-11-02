<?php

include __DIR__ . '/../../sdks/php/src/Appwrite/Client.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Service.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/InputFile.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Query.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Permission.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Role.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/ID.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/AppwriteException.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Services/Foo.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Services/Bar.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Services/General.php';

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\InputFile;
use Appwrite\Query;
use Appwrite\Permission;
use Appwrite\Role;
use Appwrite\ID;
use Appwrite\Services\Bar;
use Appwrite\Services\Foo;
use Appwrite\Services\General;

$client = new Client();
$foo = new Foo($client);
$bar = new Bar($client);
$general = new General($client);

$client->addHeader('Origin', 'http://localhost');

echo "\nTest Started\n";

// Foo Service

$response = $foo->get('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->post('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->put('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->patch('string', 123, ['string in array']);
echo "{$response['result']}\n";

$response = $foo->delete('string', 123, ['string in array']);
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

// Query helper tests
echo Query::equal('released', [true]) . "\n";
echo Query::equal('title', ['Spiderman', 'Dr. Strange']) . "\n";
echo Query::notEqual('title', 'Spiderman') . "\n";
echo Query::lessThan('releasedYear', 1990) . "\n";
echo Query::greaterThan('releasedYear', 1990) . "\n";
echo Query::search('name', "john") . "\n";
echo Query::orderAsc("title") . "\n";
echo Query::orderDesc("title") . "\n";
echo Query::cursorAfter("my_movie_id") . "\n";
echo Query::cursorBefore("my_movie_id") . "\n";
echo Query::limit(50) . "\n";
echo Query::offset(20) . "\n";

// Permission & Role helper tests
echo Permission::read(Role::any()) . "\n";
echo Permission::write(Role::user(ID::custom('userid'))) . "\n";
echo Permission::create(Role::users()) . "\n";
echo Permission::update(Role::guests()) . "\n";
echo Permission::delete(Role::team('teamId', 'owner')) . "\n";
echo Permission::delete(Role::team('teamId')) . "\n";
echo Permission::create(Role::member('memberId')) . "\n";
echo Permission::update(Role::users('verified')) . "\n";
echo Permission::update(Role::user(ID::custom('userid'), 'unverified')) . "\n";

// ID helper tests
echo ID::unique() . "\n";
echo ID::custom('custom_id') . "\n";

$response = $general->headers();
echo "{$response['result']}\n";
