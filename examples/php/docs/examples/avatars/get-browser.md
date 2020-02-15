<?php

use Appwrite\Client;
use Appwrite\Services\Avatars;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2')
;

$avatars = new Avatars($client);

$result = $avatars->getBrowser('aa');