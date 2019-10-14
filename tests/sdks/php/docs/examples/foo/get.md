<?php

use Appwrite\Client;
use Appwrite\Services\Foo;

$client = new Client();

$client
;

$foo = new Foo($client);

$result = $foo->get('[]', null);