<?php

use Appwrite\Client;
use Appwrite\Services\Teams;

$client = new Client();

$client
    ->setProject('')
;

$teams = new Teams($client);

$result = $teams->update('[TEAM_ID]', '[NAME]');