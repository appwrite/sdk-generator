<?php

use Appwrite\Client;
use Appwrite\Services\Account;

$client = new Client();

$account = new Account($client);

$result = $account->createOAuthSession('bitbucket', 'https://example.com', 'https://example.com');