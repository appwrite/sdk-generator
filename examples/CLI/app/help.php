<?php

namespace Appwrite\Services;

require_once './vendor/autoload.php';

use Utopia\CLI\Console;
use Appwrite\Parser;

$parser = new Parser();

Console::log("\e[0;31;m 
    _                            _ _           ___   __   _____ 
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
       |_|   |_|                                                  
  \e[0m") ;
Console::log("\nUsage : appwrite [SERVICE] [COMMAND] --[OPTION]\n");
Console::log("Services :");
$commands = [
        "account" => "The Account service allows you to authenticate and manage a user account.",
        "avatars" => "The Avatars service aims to help you complete everyday tasks related to your app image, icons, and avatars.",
        "database" => "The Database service allows you to create structured collections of documents, query and filter lists of documents",
        "functions" => "The Functions Service allows you view, create and manage your Cloud Functions.",
        "health" => "The Health service allows you to both validate and monitor your Appwrite server's health.",
        "locale" => "The Locale service allows you to customize your app based on your users' location.",
        "storage" => "The Storage service allows you to manage your project files.",
        "teams" => "The Teams service allows you to group users of your project and to enable them to share read and write access to your project resources",
        "users" => "The Users service allows you to manage your project users.",
        "client" => "The Client service allows you to set preferences of your Appwrite CLI",
        "init" => "Init allows you to reset your Appwrite CLI."
        ];
$parser->formatArray($commands);
Console::log("\nRun 'appwrite [SERVICE] help' for more information on a service.");
