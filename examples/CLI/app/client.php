<?php

namespace Appwrite\Services;

require_once './vendor/autoload.php';

use Appwrite\Client;
use Utopia\CLI\CLI;
use Utopia\Validator\Wildcard;
use Utopia\CLI\Console;
use Appwrite\Parser;

$client = new Client();
$cli = new CLI();
$parser = new Parser();

$cli->
      init(function() use ($cli, $parser) {
        
        if (array_key_exists('help', $cli->getArgs())) {
            $taskName = $cli->match()->getName();
            $task = $cli->getTasks()[$taskName];
            $description = $task->getLabel('description', '');
            $params = $task->getParams();

            Console::log("\e[0;31;m 
    _                            _ _           ___   __   _____ 
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
       |_|   |_|                                                  
  \e[0m") ;
            Console::log("\nUsage : appwrite client {$taskName} --[OPTIONS] \n");
            Console::log($description);
            Console::log("Options:");
            array_walk($params, function(&$key) {
                $key = $key['description'];
            });
            $parser->formatArray($params);
            Console::exit(0);
        }
      });

$cli
    ->task('setEndpoint')
    ->param('endpoint', '', new Wildcard(), 'Your Appwrite endpoint', false)
    ->action(function($endpoint) use ($client) {
        $result = $client->setPreference('endpoint', $endpoint)
                         ->savePreferences();
        if ($result === false) {
            Console::error('❌ Could not save preferences.');
        } else {
            Console::success('✅ Preferences saved successfully');
        }
    });

$cli
    ->task('setSelfSigned')
    ->param('value', '', new Wildcard(), 'A boolean representing whether you are using a self signed certificate', false)
    ->action(function($value) use ($client) {
        $result = $client->setPreference('selfSigned', $value)
                         ->savePreferences();
        if ($result === false) {
            Console::error('❌ Could not save preferences.');
        } else {
            Console::success('✅ Preferences saved successfully');
        }
    });


$cli
    ->task('setProject')
    ->param('project', '', new Wildcard(), 'Your project ID', false)
    ->action(function($project) use ($client) {
        $result = $client->setPreference('X-Appwrite-Project', $project)
                         ->savePreferences();
        if ($result === false) {
            Console::error('❌ Could not save preferences.');
        } else {
            Console::success('✅ Preferences saved successfully');
        }
    });
$cli
    ->task('setKey')
    ->param('key', '', new Wildcard(), 'Your secret API key', false)
    ->action(function($key) use ($client) {
        $result = $client->setPreference('X-Appwrite-Key', $key)
                         ->savePreferences();
        if ($result === false) {
            Console::error('❌ Could not save preferences.');
        } else {
            Console::success('✅ Preferences saved successfully');
        }
    });
$cli
    ->task('setLocale')
    ->param('locale', '', new Wildcard(), '', false)
    ->action(function($locale) use ($client) {
        $result = $client->setPreference('X-Appwrite-Locale', $locale)
                         ->savePreferences();
        if ($result === false) {
            Console::error('❌ Could not save preferences.');
        } else {
            Console::success('✅ Preferences saved successfully');
        }
    });

$cli
    ->task('version')
    ->action(function() {
       Console::log('CLI Version : 0.0.1');
       Console::log('Server Version : 0.11.0');
    });


$cli
    ->task('help')
    ->action(function() use ($parser) {
        Console::log("\e[0;31;m 
    _                            _ _           ___   __   _____ 
   /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
  //_\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
 /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
 \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
       |_|   |_|                                                  
  \e[0m") ;
        Console::log("\nUsage : appwrite client [COMMAND]\n");
        Console::log("Commands :");
        $commands = [
            "setEndpoint" => "Set your server endpoint.",
            "setProject" => "Set the project you want to connect to.",
            "setKey" => "Set the API key for the project.",
            "setLocale" => "Set your preferred locale (eg: en-US).",
            "setSelfSigned" => "Set whether you are using a self signed certificate",
            "version" => "Get the current cli version"
        ];
        $parser->formatArray($commands);
        Console::log("\nRun 'appwrite client COMMAND --help' for more information on a command.");
    });
    

$cli->run();