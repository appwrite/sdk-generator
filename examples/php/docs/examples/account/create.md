<?php

use Appwrite\Client;
use Appwrite\Services\Account;

$client = new Client();

$account = new Account($client);

$result = $account->create('email@example.com', 'password');