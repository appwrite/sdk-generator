<?php

use Appwrite\Client;
use Appwrite\Services\Account;

$client = new Client();

$client
    ->setEndpoint('https://[HOSTNAME_OR_IP]/v1') // Your API Endpoint
    ->setProject('5df5acd0d48c2') // Your project ID
;

$account = new Account($client);

$result = $account->updateMagicURLSession("[USER_ID]", "[SECRET]");
