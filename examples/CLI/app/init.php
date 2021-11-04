<?php

namespace Appwrite\Services;

require_once './vendor/autoload.php';

use Exception;
use Utopia\CLI\CLI;
use Utopia\Validator\Wildcard;
use Utopia\CLI\Console;
use Appwrite\Parser;

const USER_PREFERENCES_FILE = __DIR__."/.preferences/.prefs.json";
const PREFERENCE_ENDPOINT = "endpoint";

/**
 * Default User Preferences
 *
 * @var array
 */
$preferences = [
    PREFERENCE_ENDPOINT => '',
    'X-Appwrite-Project' => '',
    'X-Appwrite-Key' => '',
    'X-Appwrite-Locale' => '',
];


/**
 * Function to load user preferences from
 * environment variables
 */
function loadEnvVariables(): bool
{
    try {
        
        $endpoint = getenv(PREFERENCE_ENDPOINT) ?: '';
        setPreference(PREFERENCE_ENDPOINT, $endpoint);

        $project = getenv('X-Appwrite-Project') ?: '';
        setPreference('X-Appwrite-Project', $project);

        $key = getenv('X-Appwrite-Key') ?: '';
        setPreference('X-Appwrite-Key', $key);

        $locale = getenv('X-Appwrite-Locale') ?: '';
        setPreference('X-Appwrite-Locale', $locale);


        if (!isPreferenceLoaded()) {
            return false;
        }

        $result = savePreferences();
        if ($result === false) {
            return false;
        } else {
            Console::success('✅ Preferences saved successfully');
        }
        
    } catch (Exception $e) {
        return false;
    }

    return true;
}

function isPreferenceLoaded() : bool {
    if(empty(getPreference(PREFERENCE_ENDPOINT))) return false;
    if(empty(getPreference('X-Appwrite-Project'))) return false;
    if(empty(getPreference('X-Appwrite-Key'))) return false;
    if(empty(getPreference('X-Appwrite-Locale'))) return false;
    return true;
}

/**
 * Function to write user preferences to
 * the JSON file
 * 
 * @return int
 */
function savePreferences(string $filename = USER_PREFERENCES_FILE): int
{
    global $preferences;
    $jsondata = json_encode($preferences, JSON_PRETTY_PRINT);
    $result = file_put_contents($filename, $jsondata);
    return $result;
}

function getPreference(string $key): string
{
    global $preferences;
    return $preferences[$key] ?? '';
}

function setPreference(string $key , string $value) 
{
    global $preferences;
    $preferences[$key] = $value;
}

function promptUser()
{
    Console::info("🟢 Starting prompt\n");

    if(empty(getPreference(PREFERENCE_ENDPOINT))) {
        $endpoint = Console::confirm('🟢 Choose your API Endpoint: ( default: http://localhost/v1 )');
        setPreference(PREFERENCE_ENDPOINT, empty($endpoint) ? 'http://localhost/v1' : $endpoint);
    }

    if(empty(getPreference('X-Appwrite-Project'))) {
        $project = Console::confirm('🟢 Enter your project from the Appwrite console: ');
        if (empty($project)) {
            Console::error("❌ You cannot continue without a project. Exiting...");
            exit();
        } 
        setPreference('X-Appwrite-Project', $project);
    }

    if(empty(getPreference('X-Appwrite-Key'))) {
        $key = Console::confirm('🟢 Enter your key from the Appwrite console: ');
        if (empty($key)) {
            Console::error("❌ You cannot continue without a key. Exiting...");
            exit();
        } 
        setPreference('X-Appwrite-Key', $key);
    }

    if(empty(getPreference('X-Appwrite-Locale'))) {
        $locale = Console::confirm('🟢 Enter your locale: : ( default: en-US )');
        setPreference('X-Appwrite-Locale', empty($locale) ? 'en-US' : $locale );
    }
    
    $result = savePreferences();
    if ($result === false) {
        throw new Exception('❌ Could not save preferences.');
    } else {
        Console::success('✅ Preferences saved successfully');
    }
}

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
            Console::log("\nUsage : appwrite {$taskName} --[OPTIONS] \n");
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
    ->task('init')
    ->label('description', "The init command is used to initialize your CLI\n")
    ->param('endpoint', '', new Wildcard(), 'Your Appwrite endpoint', true)
    ->param('project', '', new Wildcard(), 'Your project ID', true)
    ->param('key', '', new Wildcard(), 'Your secret API key', true)
    ->param('locale', '', new Wildcard(), '', true)
    ->action(function( $endpoint, $project, $key, $locale ) {
        /* 
        * Check if environment variables exist
        * Else prompt the user
        */
        
        putenv("endpoint=$endpoint");
        putenv("X-Appwrite-Project=$project");
        putenv("X-Appwrite-Key=$key");
        putenv("X-Appwrite-Locale=$locale");

        if (!loadEnvVariables()) {
            promptUser();
        }
});

$cli->run();
