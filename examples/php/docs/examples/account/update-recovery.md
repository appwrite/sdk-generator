<?php

use Appwrite\Client;
use Appwrite\Services\Account;

$client = new Client();

$account = new Account($client);

$result = $account->updateRecovery('[USER_ID]', '[SECRET]', 'password', 'password');