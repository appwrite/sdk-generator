<?php

include __DIR__ . '/../../sdks/php/src/Appwrite/Client.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Service.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/InputFile.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Query.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Permission.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Role.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/ID.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Operator.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/AppwriteException.php';
include __DIR__ . '/../../sdks/php/src/Appwrite/Enums/MockType.php';
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
use Appwrite\Operator;
use Appwrite\Condition;
use Appwrite\Enums\MockType;
use Appwrite\Services\Bar;
use Appwrite\Services\Foo;
use Appwrite\Services\General;

$client = (new Client())
    ->addHeader("Origin", "http://localhost")
    ->setSelfSigned();

$foo = new Foo($client);
$bar = new Bar($client);
$general = new General($client);

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

$response = $general->enum(MockType::FIRST());
echo "{$response['result']}\n";

try {
    $response = $general->error400();
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
    echo "{$e->getResponse()}\n";
}

try {
    $response = $general->error500();
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
    echo "{$e->getResponse()}\n";
}

try {
    $response = $general->error502();
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
    echo "{$e->getResponse()}\n";
}

try {
    $client->setEndpoint("htp://cloud.appwrite.io/v1");
} catch (AppwriteException $e) {
    echo "{$e->getMessage()}\n";
}

$general->empty();

$url = $general->oauth2(
    'clientId',
    ['test'],
    '123456',
    'https://localhost',
    'https://localhost'
);
echo $url . "\n";

// Query helper tests
echo Query::equal('released', [true]) . "\n";
echo Query::equal('title', ['Spiderman', 'Dr. Strange']) . "\n";
echo Query::notEqual('title', 'Spiderman') . "\n";
echo Query::lessThan('releasedYear', 1990) . "\n";
echo Query::greaterThan('releasedYear', 1990) . "\n";
echo Query::search('name', 'john') . "\n";
echo Query::isNull('name') . "\n";
echo Query::isNotNull('name') . "\n";
echo Query::between('age', 50, 100) . "\n";
echo Query::between('age', 50.5, 100.5) . "\n";
echo Query::between('name', 'Anna', 'Brad') . "\n";
echo Query::startsWith('name', 'Ann') . "\n";
echo Query::endsWith('name', 'nne') . "\n";
echo Query::select(['name', 'age']) . "\n";
echo Query::orderAsc('title') . "\n";
echo Query::orderDesc('title') . "\n";
echo Query::orderRandom() . "\n";
echo Query::cursorAfter('my_movie_id') . "\n";
echo Query::cursorBefore('my_movie_id') . "\n";
echo Query::limit(50) . "\n";
echo Query::offset(20) . "\n";
echo Query::contains('title', ['Spider']) . "\n";
echo Query::contains('labels', ['first']) . "\n";

// New query methods
echo Query::notContains('title', ['Spider']) . "\n";
echo Query::notSearch('name', 'john') . "\n";
echo Query::notBetween('age', 50, 100) . "\n";
echo Query::notStartsWith('name', 'Ann') . "\n";
echo Query::notEndsWith('name', 'nne') . "\n";
echo Query::createdBefore('2023-01-01') . "\n";
echo Query::createdAfter('2023-01-01') . "\n";
echo Query::createdBetween('2023-01-01', '2023-12-31') . "\n";
echo Query::updatedBefore('2023-01-01') . "\n";
echo Query::updatedAfter('2023-01-01') . "\n";
echo Query::updatedBetween('2023-01-01', '2023-12-31') . "\n";

// Spatial Distance query tests
echo Query::distanceEqual('location', [[40.7128, -74], [40.7128, -74]], 1000) . "\n";
echo Query::distanceEqual('location', [40.7128, -74], 1000, true) . "\n";
echo Query::distanceNotEqual('location', [40.7128, -74], 1000) . "\n";
echo Query::distanceNotEqual('location', [40.7128, -74], 1000, true) . "\n";
echo Query::distanceGreaterThan('location', [40.7128, -74], 1000) . "\n";
echo Query::distanceGreaterThan('location', [40.7128, -74], 1000, true) . "\n";
echo Query::distanceLessThan('location', [40.7128, -74], 1000) . "\n";
echo Query::distanceLessThan('location', [40.7128, -74], 1000, true) . "\n";

// Spatial query tests
echo Query::intersects('location', [40.7128, -74]) . "\n";
echo Query::notIntersects('location', [40.7128, -74]) . "\n";
echo Query::crosses('location', [40.7128, -74]) . "\n";
echo Query::notCrosses('location', [40.7128, -74]) . "\n";
echo Query::overlaps('location', [40.7128, -74]) . "\n";
echo Query::notOverlaps('location', [40.7128, -74]) . "\n";
echo Query::touches('location', [40.7128, -74]) . "\n";
echo Query::notTouches('location', [40.7128, -74]) . "\n";
echo Query::contains('location', [[40.7128, -74], [40.7128, -74]]) . "\n";
echo Query::notContains('location', [[40.7128, -74], [40.7128, -74]]) . "\n";
echo Query::equal('location', [[40.7128, -74], [40.7128, -74]]) . "\n";
echo Query::notEqual('location', [[40.7128, -74], [40.7128, -74]]) . "\n";

echo Query::or([
    Query::equal('released', [true]),
    Query::lessThan('releasedYear', 1990)
]) . "\n";
echo Query::and([
    Query::equal('released', [false]),
    Query::greaterThan('releasedYear', 2015)
]) . "\n";

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
echo Permission::create(Role::label('admin')) . "\n";

// ID helper tests
echo ID::unique() . "\n";
echo ID::custom('custom_id') . "\n";

// Operator helper tests
echo Operator::increment() . "\n";
echo Operator::increment(5, 100) . "\n";
echo Operator::decrement() . "\n";
echo Operator::decrement(3, 0) . "\n";
echo Operator::multiply(2) . "\n";
echo Operator::multiply(3, 1000) . "\n";
echo Operator::divide(2) . "\n";
echo Operator::divide(4, 1) . "\n";
echo Operator::modulo(5) . "\n";
echo Operator::power(2) . "\n";
echo Operator::power(3, 100) . "\n";
echo Operator::arrayAppend(['item1', 'item2']) . "\n";
echo Operator::arrayPrepend(['first', 'second']) . "\n";
echo Operator::arrayInsert(0, 'newItem') . "\n";
echo Operator::arrayRemove('oldItem') . "\n";
echo Operator::arrayUnique() . "\n";
echo Operator::arrayIntersect(['a', 'b', 'c']) . "\n";
echo Operator::arrayDiff(['x', 'y']) . "\n";
echo Operator::arrayFilter(Condition::Equal, 'test') . "\n";
echo Operator::concat('suffix') . "\n";
echo Operator::replace('old', 'new') . "\n";
echo Operator::toggle() . "\n";
echo Operator::dateAddDays(7) . "\n";
echo Operator::dateSubDays(3) . "\n";
echo Operator::dateSetNow() . "\n";

$response = $general->headers();
echo "{$response['result']}\n";
