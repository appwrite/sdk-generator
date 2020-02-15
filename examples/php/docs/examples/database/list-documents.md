<?php

use Appwrite\Client;
use Appwrite\Services\Database;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2')
;

$database = new Database($client);

$result = $database->listDocuments('[COLLECTION_ID]');