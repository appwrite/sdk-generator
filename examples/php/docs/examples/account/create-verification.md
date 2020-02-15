<?php

use Appwrite\Client;
use Appwrite\Services\Account;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2')
;

$account = new Account($client);

$result = $account->createVerification('https://example.com');