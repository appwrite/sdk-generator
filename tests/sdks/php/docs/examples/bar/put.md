<?php

use Appwrite\Client;
use Appwrite\Services\Bar;

$client = new Client();

$client
;

$bar = new Bar($client);

$result = $bar->put('[]', null);