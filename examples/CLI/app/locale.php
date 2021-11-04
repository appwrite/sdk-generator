<?php

namespace Appwrite\Services;

require_once './vendor/autoload.php';

use Exception;
use Appwrite\Client;
use Appwrite\Parser;
use Utopia\CLI\CLI;
use Utopia\CLI\Console;
use Utopia\Validator\Wildcard;

$parser = new Parser();
$cli = new CLI();

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
            Console::log("\nUsage : appwrite locale {$taskName} --[OPTIONS] \n");
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
    ->task('get')
    ->label('description', "Get the current user location based on IP. Returns an object with user country code, country name, continent name, continent code, ip address and suggested currency. You can use the locale header to get the data in a supported language.

([IP Geolocation by DB-IP](https://db-ip.com))\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getContinents')
    ->label('description', "List of all continents. You can use the locale header to get the data in a supported language.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale/continents');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getCountries')
    ->label('description', "List of all countries. You can use the locale header to get the data in a supported language.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale/countries');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getCountriesEU')
    ->label('description', "List of all countries that are currently members of the EU. You can use the locale header to get the data in a supported language.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale/countries/eu');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getCountriesPhones')
    ->label('description', "List of all countries phone codes. You can use the locale header to get the data in a supported language.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale/countries/phones');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getCurrencies')
    ->label('description', "List of all currencies, including currency symbol, name, plural, and decimal digits for all major and minor currencies. You can use the locale header to get the data in a supported language.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale/currencies');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getLanguages')
    ->label('description', "List of all languages classified by ISO 639-1 including 2-letter code, name in English, and name in the respective language.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/locale/languages');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
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
  \e[0m");
        Console::log("\nUsage : appwrite locale [COMMAND]\n");
        Console::log("Commands :");
        $commands = [
                "get" => "Get the current user location based on IP. Returns an object with user country code, country name, continent name, continent code, ip address and suggested currency. You can use the locale header to get the data in a supported language.

([IP Geolocation by DB-IP](https://db-ip.com))",
                "getContinents" => "List of all continents. You can use the locale header to get the data in a supported language.",
                "getCountries" => "List of all countries. You can use the locale header to get the data in a supported language.",
                "getCountriesEU" => "List of all countries that are currently members of the EU. You can use the locale header to get the data in a supported language.",
                "getCountriesPhones" => "List of all countries phone codes. You can use the locale header to get the data in a supported language.",
                "getCurrencies" => "List of all currencies, including currency symbol, name, plural, and decimal digits for all major and minor currencies. You can use the locale header to get the data in a supported language.",
                "getLanguages" => "List of all languages classified by ISO 639-1 including 2-letter code, name in English, and name in the respective language.",
        ];
        $parser->formatArray($commands);
        Console::log("\nRun 'appwrite locale COMMAND --help' for more information on a command.");
    });


$cli->run();
