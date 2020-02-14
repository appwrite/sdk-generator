<?php

use Appwrite\Client;
use Appwrite\Services\Teams;

$client = new Client();

$teams = new Teams($client);

$result = $teams->updateMembershipStatus('[TEAM_ID]', '[INVITE_ID]', '[USER_ID]', '[SECRET]');