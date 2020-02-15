<?php

use Appwrite\Client;
use Appwrite\Services\Teams;

$client = new Client();

$client
    ->setProject('5df5acd0d48c2') // Your project ID
;

$teams = new Teams($client);

$result = $teams->deleteMembership('[TEAM_ID]', '[INVITE_ID]');