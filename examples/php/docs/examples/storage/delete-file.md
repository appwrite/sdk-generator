<?php

use Appwrite\Client;
use Appwrite\Services\Storage;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2') // Your project ID
;

$storage = new Storage($client);

$result = $storage->deleteFile('[FILE_ID]');