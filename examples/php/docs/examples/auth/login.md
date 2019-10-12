<?php

use Appwrite\Client;
use Appwrite\Services\Auth;

$client = new Client();

$client
    ->setProject('')
;

$auth = new Auth($client);

$result = $auth->login('email@example.com', 'password');