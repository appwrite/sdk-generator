<?php

use Appwrite\Client;
use Appwrite\Services\Locale;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2')
;

$locale = new Locale($client);

$result = $locale->getContinents();