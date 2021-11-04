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
            Console::log("\nUsage : appwrite account {$taskName} --[OPTIONS] \n");
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
    ->label('description', "Get currently logged in user data as JSON object.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/account');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('delete')
    ->label('description', "Delete a currently logged in user account. Behind the scene, the user record is not deleted but permanently blocked from any access. This is done to avoid deleted accounts being overtaken by new users with the same email address. Any user-related resources like documents or storage files should be deleted separately.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/account');
        $params = [];
        $response =  $client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateEmail')
    ->label('description', "Update currently logged in user account email address. After changing user address, user confirmation status is being reset and a new confirmation mail is sent. For security measures, user password is required to complete this request.
This endpoint can also be used to convert an anonymous account to a normal one, by passing an email address and a new password.\n\n")
    ->param('email', '' , new Wildcard() , 'User email.',  false)
    ->param('password', '' , new Wildcard() , 'User password. Must be between 6 to 32 chars.',  false)
    ->action(function ( $email, $password ) use ($parser) {
        /** @var string $email */
        /** @var string $password */

        $client = new Client();
        $path   = str_replace([], [], '/account/email');
        $params = [];
        /** Body Params */
        $params['email'] = $email;
        $params['password'] = $password;
        $response =  $client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getLogs')
    ->label('description', "Get currently logged in user list of latest security activity logs. Each log returns user IP address, location and date and time of log.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/account/logs');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateName')
    ->label('description', "Update currently logged in user account name.\n\n")
    ->param('name', '' , new Wildcard() , 'User name. Max length: 128 chars.',  false)
    ->action(function ( $name ) use ($parser) {
        /** @var string $name */

        $client = new Client();
        $path   = str_replace([], [], '/account/name');
        $params = [];
        /** Body Params */
        $params['name'] = $name;
        $response =  $client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updatePassword')
    ->label('description', "Update currently logged in user password. For validation, user is required to pass in the new password, and the old password. For users created with OAuth and Team Invites, oldPassword is optional.\n\n")
    ->param('password', '' , new Wildcard() , 'New user password. Must be between 6 to 32 chars.',  false)
    ->param('oldPassword', '' , new Wildcard() , 'Old user password. Must be between 6 to 32 chars.',  true)
    ->action(function ( $password, $oldPassword ) use ($parser) {
        /** @var string $password */
        /** @var string $oldPassword */

        $client = new Client();
        $path   = str_replace([], [], '/account/password');
        $params = [];
        /** Body Params */
        $params['password'] = $password;
        $params['oldPassword'] = $oldPassword;
        $response =  $client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getPrefs')
    ->label('description', "Get currently logged in user preferences as a key-value object.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/account/prefs');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updatePrefs')
    ->label('description', "Update currently logged in user account preferences. You can pass only the specific settings you wish to update.\n\n")
    ->param('prefs', '' , new Wildcard() , 'Prefs key-value JSON object.',  false)
    ->action(function ( $prefs ) use ($parser) {
        /** @var object $prefs */

        $client = new Client();
        $path   = str_replace([], [], '/account/prefs');
        $params = [];
        /** Body Params */
        $params['prefs'] = \json_decode($prefs);
        $response =  $client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('createRecovery')
    ->label('description', "Sends the user an email with a temporary secret key for password reset. When the user clicks the confirmation link he is redirected back to your app password reset URL with the secret key and email address values attached to the URL query string. Use the query string params to submit a request to the [PUT /account/recovery](/docs/client/account#accountUpdateRecovery) endpoint to complete the process. The verification link sent to the user's email address is valid for 1 hour.\n\n")
    ->param('email', '' , new Wildcard() , 'User email.',  false)
    ->param('url', '' , new Wildcard() , 'URL to redirect the user back to your app from the recovery email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.',  false)
    ->action(function ( $email, $url ) use ($parser) {
        /** @var string $email */
        /** @var string $url */

        $client = new Client();
        $path   = str_replace([], [], '/account/recovery');
        $params = [];
        /** Body Params */
        $params['email'] = $email;
        $params['url'] = $url;
        $response =  $client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateRecovery')
    ->label('description', "Use this endpoint to complete the user account password reset. Both the **userId** and **secret** arguments will be passed as query parameters to the redirect URL you have provided when sending your request to the [POST /account/recovery](/docs/client/account#accountCreateRecovery) endpoint.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md) the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.\n\n")
    ->param('userId', '' , new Wildcard() , 'User account UID address.',  false)
    ->param('secret', '' , new Wildcard() , 'Valid reset token.',  false)
    ->param('password', '' , new Wildcard() , 'New password. Must be between 6 to 32 chars.',  false)
    ->param('passwordAgain', '' , new Wildcard() , 'New password again. Must be between 6 to 32 chars.',  false)
    ->action(function ( $userId, $secret, $password, $passwordAgain ) use ($parser) {
        /** @var string $userId */
        /** @var string $secret */
        /** @var string $password */
        /** @var string $passwordAgain */

        $client = new Client();
        $path   = str_replace([], [], '/account/recovery');
        $params = [];
        /** Body Params */
        $params['userId'] = $userId;
        $params['secret'] = $secret;
        $params['password'] = $password;
        $params['passwordAgain'] = $passwordAgain;
        $response =  $client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getSessions')
    ->label('description', "Get currently logged in user list of active sessions across different devices.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/account/sessions');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('deleteSessions')
    ->label('description', "Delete all sessions from the user account and remove any sessions cookies from the end client.\n\n")
    ->action(function ( ) use ($parser) {

        $client = new Client();
        $path   = str_replace([], [], '/account/sessions');
        $params = [];
        $response =  $client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getSession')
    ->label('description', "Use this endpoint to get a logged in user's session using a Session ID. Inputting 'current' will return the current session being used.\n\n")
    ->param('sessionId', '' , new Wildcard() , 'Session unique ID. Use the string &#039;current&#039; to get the current device session.',  false)
    ->action(function ( $sessionId ) use ($parser) {
        /** @var string $sessionId */

        $client = new Client();
        $path   = str_replace(['{sessionId}'], [$sessionId], '/account/sessions/{sessionId}');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('deleteSession')
    ->label('description', "Use this endpoint to log out the currently logged in user from all their account sessions across all of their different devices. When using the option id argument, only the session unique ID provider will be deleted.\n\n")
    ->param('sessionId', '' , new Wildcard() , 'Session unique ID. Use the string &#039;current&#039; to delete the current device session.',  false)
    ->action(function ( $sessionId ) use ($parser) {
        /** @var string $sessionId */

        $client = new Client();
        $path   = str_replace(['{sessionId}'], [$sessionId], '/account/sessions/{sessionId}');
        $params = [];
        $response =  $client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('createVerification')
    ->label('description', "Use this endpoint to send a verification message to your user email address to confirm they are the valid owners of that address. Both the **userId** and **secret** arguments will be passed as query parameters to the URL you have provided to be attached to the verification email. The provided URL should redirect the user back to your app and allow you to complete the verification process by verifying both the **userId** and **secret** parameters. Learn more about how to [complete the verification process](/docs/client/account#accountUpdateVerification). The verification link sent to the user's email address is valid for 7 days.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md), the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.
\n\n")
    ->param('url', '' , new Wildcard() , 'URL to redirect the user back to your app from the verification email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.',  false)
    ->action(function ( $url ) use ($parser) {
        /** @var string $url */

        $client = new Client();
        $path   = str_replace([], [], '/account/verification');
        $params = [];
        /** Body Params */
        $params['url'] = $url;
        $response =  $client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateVerification')
    ->label('description', "Use this endpoint to complete the user email verification process. Use both the **userId** and **secret** parameters that were attached to your app URL to verify the user email ownership. If confirmed this route will return a 200 status code.\n\n")
    ->param('userId', '' , new Wildcard() , 'User unique ID.',  false)
    ->param('secret', '' , new Wildcard() , 'Valid verification token.',  false)
    ->action(function ( $userId, $secret ) use ($parser) {
        /** @var string $userId */
        /** @var string $secret */

        $client = new Client();
        $path   = str_replace([], [], '/account/verification');
        $params = [];
        /** Body Params */
        $params['userId'] = $userId;
        $params['secret'] = $secret;
        $response =  $client->call(Client::METHOD_PUT, $path, [
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
        Console::log("\nUsage : appwrite account [COMMAND]\n");
        Console::log("Commands :");
        $commands = [
                "get" => "Get currently logged in user data as JSON object.",
                "delete" => "Delete a currently logged in user account. Behind the scene, the user record is not deleted but permanently blocked from any access. This is done to avoid deleted accounts being overtaken by new users with the same email address. Any user-related resources like documents or storage files should be deleted separately.",
                "updateEmail" => "Update currently logged in user account email address. After changing user address, user confirmation status is being reset and a new confirmation mail is sent. For security measures, user password is required to complete this request.
This endpoint can also be used to convert an anonymous account to a normal one, by passing an email address and a new password.",
                "getLogs" => "Get currently logged in user list of latest security activity logs. Each log returns user IP address, location and date and time of log.",
                "updateName" => "Update currently logged in user account name.",
                "updatePassword" => "Update currently logged in user password. For validation, user is required to pass in the new password, and the old password. For users created with OAuth and Team Invites, oldPassword is optional.",
                "getPrefs" => "Get currently logged in user preferences as a key-value object.",
                "updatePrefs" => "Update currently logged in user account preferences. You can pass only the specific settings you wish to update.",
                "createRecovery" => "Sends the user an email with a temporary secret key for password reset. When the user clicks the confirmation link he is redirected back to your app password reset URL with the secret key and email address values attached to the URL query string. Use the query string params to submit a request to the [PUT /account/recovery](/docs/client/account#accountUpdateRecovery) endpoint to complete the process. The verification link sent to the user's email address is valid for 1 hour.",
                "updateRecovery" => "Use this endpoint to complete the user account password reset. Both the **userId** and **secret** arguments will be passed as query parameters to the redirect URL you have provided when sending your request to the [POST /account/recovery](/docs/client/account#accountCreateRecovery) endpoint.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md) the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.",
                "getSessions" => "Get currently logged in user list of active sessions across different devices.",
                "deleteSessions" => "Delete all sessions from the user account and remove any sessions cookies from the end client.",
                "getSession" => "Use this endpoint to get a logged in user's session using a Session ID. Inputting 'current' will return the current session being used.",
                "deleteSession" => "Use this endpoint to log out the currently logged in user from all their account sessions across all of their different devices. When using the option id argument, only the session unique ID provider will be deleted.",
                "createVerification" => "Use this endpoint to send a verification message to your user email address to confirm they are the valid owners of that address. Both the **userId** and **secret** arguments will be passed as query parameters to the URL you have provided to be attached to the verification email. The provided URL should redirect the user back to your app and allow you to complete the verification process by verifying both the **userId** and **secret** parameters. Learn more about how to [complete the verification process](/docs/client/account#accountUpdateVerification). The verification link sent to the user's email address is valid for 7 days.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md), the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.
",
                "updateVerification" => "Use this endpoint to complete the user email verification process. Use both the **userId** and **secret** parameters that were attached to your app URL to verify the user email ownership. If confirmed this route will return a 200 status code.",
        ];
        $parser->formatArray($commands);
        Console::log("\nRun 'appwrite account COMMAND --help' for more information on a command.");
    });


$cli->run();
