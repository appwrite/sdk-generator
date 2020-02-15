<?php

use Appwrite\Client;
use Appwrite\Services\Database;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2') // Your project ID
;

$database = new Database($client);

$result = $database->deleteDocument('[COLLECTION_ID]', '[DOCUMENT_ID]');