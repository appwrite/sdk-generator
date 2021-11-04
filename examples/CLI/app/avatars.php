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
            Console::log("\nUsage : appwrite avatars {$taskName} --[OPTIONS] \n");
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
    ->task('getBrowser')
    ->label('description', "You can use this endpoint to show different browser icons to your users. The code argument receives the browser code as it appears in your user /account/sessions endpoint. Use width, height and quality arguments to change the output settings.\n\n")
    ->param('code', '' , new Wildcard() , 'Browser Code.',  false)
    ->param('width', 100 , new Wildcard() , 'Image width. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('height', 100 , new Wildcard() , 'Image height. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('quality', 100 , new Wildcard() , 'Image quality. Pass an integer between 0 to 100. Defaults to 100.',  true)
    ->action(function ( $code, $width, $height, $quality ) use ($parser) {
        /** @var string $code */
        /** @var integer $width */
        /** @var integer $height */
        /** @var integer $quality */

        $client = new Client();
        $path   = str_replace(['{code}'], [$code], '/avatars/browsers/{code}');
        $params = [];
        /** Query Params */
        $params['width'] = $width;
        $params['height'] = $height;
        $params['quality'] = $quality;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getCreditCard')
    ->label('description', "The credit card endpoint will return you the icon of the credit card provider you need. Use width, height and quality arguments to change the output settings.\n\n")
    ->param('code', '' , new Wildcard() , 'Credit Card Code. Possible values: amex, argencard, cabal, censosud, diners, discover, elo, hipercard, jcb, mastercard, naranja, targeta-shopping, union-china-pay, visa, mir, maestro.',  false)
    ->param('width', 100 , new Wildcard() , 'Image width. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('height', 100 , new Wildcard() , 'Image height. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('quality', 100 , new Wildcard() , 'Image quality. Pass an integer between 0 to 100. Defaults to 100.',  true)
    ->action(function ( $code, $width, $height, $quality ) use ($parser) {
        /** @var string $code */
        /** @var integer $width */
        /** @var integer $height */
        /** @var integer $quality */

        $client = new Client();
        $path   = str_replace(['{code}'], [$code], '/avatars/credit-cards/{code}');
        $params = [];
        /** Query Params */
        $params['width'] = $width;
        $params['height'] = $height;
        $params['quality'] = $quality;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getFavicon')
    ->label('description', "Use this endpoint to fetch the favorite icon (AKA favicon) of any remote website URL.
\n\n")
    ->param('url', '' , new Wildcard() , 'Website URL which you want to fetch the favicon from.',  false)
    ->action(function ( $url ) use ($parser) {
        /** @var string $url */

        $client = new Client();
        $path   = str_replace([], [], '/avatars/favicon');
        $params = [];
        /** Query Params */
        $params['url'] = $url;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getFlag')
    ->label('description', "You can use this endpoint to show different country flags icons to your users. The code argument receives the 2 letter country code. Use width, height and quality arguments to change the output settings.\n\n")
    ->param('code', '' , new Wildcard() , 'Country Code. ISO Alpha-2 country code format.',  false)
    ->param('width', 100 , new Wildcard() , 'Image width. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('height', 100 , new Wildcard() , 'Image height. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('quality', 100 , new Wildcard() , 'Image quality. Pass an integer between 0 to 100. Defaults to 100.',  true)
    ->action(function ( $code, $width, $height, $quality ) use ($parser) {
        /** @var string $code */
        /** @var integer $width */
        /** @var integer $height */
        /** @var integer $quality */

        $client = new Client();
        $path   = str_replace(['{code}'], [$code], '/avatars/flags/{code}');
        $params = [];
        /** Query Params */
        $params['width'] = $width;
        $params['height'] = $height;
        $params['quality'] = $quality;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getImage')
    ->label('description', "Use this endpoint to fetch a remote image URL and crop it to any image size you want. This endpoint is very useful if you need to crop and display remote images in your app or in case you want to make sure a 3rd party image is properly served using a TLS protocol.\n\n")
    ->param('url', '' , new Wildcard() , 'Image URL which you want to crop.',  false)
    ->param('width', 400 , new Wildcard() , 'Resize preview image width, Pass an integer between 0 to 2000.',  true)
    ->param('height', 400 , new Wildcard() , 'Resize preview image height, Pass an integer between 0 to 2000.',  true)
    ->action(function ( $url, $width, $height ) use ($parser) {
        /** @var string $url */
        /** @var integer $width */
        /** @var integer $height */

        $client = new Client();
        $path   = str_replace([], [], '/avatars/image');
        $params = [];
        /** Query Params */
        $params['url'] = $url;
        $params['width'] = $width;
        $params['height'] = $height;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getInitials')
    ->label('description', "Use this endpoint to show your user initials avatar icon on your website or app. By default, this route will try to print your logged-in user name or email initials. You can also overwrite the user name if you pass the 'name' parameter. If no name is given and no user is logged, an empty avatar will be returned.

You can use the color and background params to change the avatar colors. By default, a random theme will be selected. The random theme will persist for the user's initials when reloading the same theme will always return for the same initials.\n\n")
    ->param('name', '' , new Wildcard() , 'Full Name. When empty, current user name or email will be used. Max length: 128 chars.',  true)
    ->param('width', 500 , new Wildcard() , 'Image width. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('height', 500 , new Wildcard() , 'Image height. Pass an integer between 0 to 2000. Defaults to 100.',  true)
    ->param('color', '' , new Wildcard() , 'Changes text color. By default a random color will be picked and stay will persistent to the given name.',  true)
    ->param('background', '' , new Wildcard() , 'Changes background color. By default a random color will be picked and stay will persistent to the given name.',  true)
    ->action(function ( $name, $width, $height, $color, $background ) use ($parser) {
        /** @var string $name */
        /** @var integer $width */
        /** @var integer $height */
        /** @var string $color */
        /** @var string $background */

        $client = new Client();
        $path   = str_replace([], [], '/avatars/initials');
        $params = [];
        /** Query Params */
        $params['name'] = $name;
        $params['width'] = $width;
        $params['height'] = $height;
        $params['color'] = $color;
        $params['background'] = $background;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getQR')
    ->label('description', "Converts a given plain text to a QR code image. You can use the query parameters to change the size and style of the resulting image.\n\n")
    ->param('text', '' , new Wildcard() , 'Plain text to be converted to QR code image.',  false)
    ->param('size', 400 , new Wildcard() , 'QR code size. Pass an integer between 0 to 1000. Defaults to 400.',  true)
    ->param('margin', 1 , new Wildcard() , 'Margin from edge. Pass an integer between 0 to 10. Defaults to 1.',  true)
    ->param('download', false , new Wildcard() , 'Return resulting image with &#039;Content-Disposition: attachment &#039; headers for the browser to start downloading it. Pass 0 for no header, or 1 for otherwise. Default value is set to 0.',  true)
    ->action(function ( $text, $size, $margin, $download ) use ($parser) {
        /** @var string $text */
        /** @var integer $size */
        /** @var integer $margin */
        /** @var boolean $download */

        $client = new Client();
        $path   = str_replace([], [], '/avatars/qr');
        $params = [];
        /** Query Params */
        $params['text'] = $text;
        $params['size'] = $size;
        $params['margin'] = $margin;
        $params['download'] = $download;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
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
        Console::log("\nUsage : appwrite avatars [COMMAND]\n");
        Console::log("Commands :");
        $commands = [
                "getBrowser" => "You can use this endpoint to show different browser icons to your users. The code argument receives the browser code as it appears in your user /account/sessions endpoint. Use width, height and quality arguments to change the output settings.",
                "getCreditCard" => "The credit card endpoint will return you the icon of the credit card provider you need. Use width, height and quality arguments to change the output settings.",
                "getFavicon" => "Use this endpoint to fetch the favorite icon (AKA favicon) of any remote website URL.
",
                "getFlag" => "You can use this endpoint to show different country flags icons to your users. The code argument receives the 2 letter country code. Use width, height and quality arguments to change the output settings.",
                "getImage" => "Use this endpoint to fetch a remote image URL and crop it to any image size you want. This endpoint is very useful if you need to crop and display remote images in your app or in case you want to make sure a 3rd party image is properly served using a TLS protocol.",
                "getInitials" => "Use this endpoint to show your user initials avatar icon on your website or app. By default, this route will try to print your logged-in user name or email initials. You can also overwrite the user name if you pass the 'name' parameter. If no name is given and no user is logged, an empty avatar will be returned.

You can use the color and background params to change the avatar colors. By default, a random theme will be selected. The random theme will persist for the user's initials when reloading the same theme will always return for the same initials.",
                "getQR" => "Converts a given plain text to a QR code image. You can use the query parameters to change the size and style of the resulting image.",
        ];
        $parser->formatArray($commands);
        Console::log("\nRun 'appwrite avatars COMMAND --help' for more information on a command.");
    });


$cli->run();
