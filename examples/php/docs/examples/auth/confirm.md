<?php

use Appwrite\Client;
use Appwrite\Services\Auth;

$client = new Client();

$auth = new Auth($client);

$result = $auth->confirm('[USER_ID]', '[TOKEN]');