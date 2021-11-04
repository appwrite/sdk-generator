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
            Console::log("\nUsage : appwrite storage {$taskName} --[OPTIONS] \n");
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
    ->task('listFiles')
    ->label('description', "Get a list of all the user files. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project's files. [Learn more about different API modes](/docs/admin).\n\n")
    ->param('search', '' , new Wildcard() , 'Search term to filter your list results. Max length: 256 chars.',  true)
    ->param('limit', 25 , new Wildcard() , 'Results limit value. By default will return maximum 25 results. Maximum of 100 results allowed per request.',  true)
    ->param('offset', 0 , new Wildcard() , 'Results offset. The default value is 0. Use this param to manage pagination.',  true)
    ->param('orderType', 'ASC' , new Wildcard() , 'Order result by ASC or DESC order.',  true)
    ->action(function ( $search, $limit, $offset, $orderType ) use ($parser) {
        /** @var string $search */
        /** @var integer $limit */
        /** @var integer $offset */
        /** @var string $orderType */

        $client = new Client();
        $path   = str_replace([], [], '/storage/files');
        $params = [];
        /** Query Params */
        $params['search'] = $search;
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        $params['orderType'] = $orderType;
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('createFile')
    ->label('description', "Create a new file. The user who creates the file will automatically be assigned to read and write access unless he has passed custom values for read and write arguments.\n\n")
    ->param('file', '' , new Wildcard() , 'Binary file.',  false)
    ->param('read', [] , new Wildcard() , 'An array of strings with read permissions. By default only the current user is granted with read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->param('write', [] , new Wildcard() , 'An array of strings with write permissions. By default only the current user is granted with write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->action(function ( $file, $read, $write ) use ($parser) {
        /** @var file $file */
        /** @var array $read */
        /** @var array $write */

        $client = new Client();
        $path   = str_replace([], [], '/storage/files');
        $params = [];
        /** Body Params */
        $file = realpath(__DIR__.'/../files/'.\urldecode($file));
        if (file_exists($file) === false ) {
            throw new Exception("Path doesn't exist. Please ensure that the path is within the current directory. "); 
        }
        $cFile = new \CURLFile($file,  'image/png' , basename($file));
        $params['file'] = $cFile;
        $params['read'] = !is_array($read) ? array($read) : $read;
        $params['write'] = !is_array($write) ? array($write) : $write;
        $response =  $client->call(Client::METHOD_POST, $path, [
            'content-type' => 'multipart/form-data',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getFile')
    ->label('description', "Get a file by its unique ID. This endpoint response returns a JSON object with the file metadata.\n\n")
    ->param('fileId', '' , new Wildcard() , 'File unique ID.',  false)
    ->action(function ( $fileId ) use ($parser) {
        /** @var string $fileId */

        $client = new Client();
        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateFile')
    ->label('description', "Update a file by its unique ID. Only users with write permissions have access to update this resource.\n\n")
    ->param('fileId', '' , new Wildcard() , 'File unique ID.',  false)
    ->param('read', '' , new Wildcard() , 'An array of strings with read permissions. By default no user is granted with any read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  false)
    ->param('write', '' , new Wildcard() , 'An array of strings with write permissions. By default no user is granted with any write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  false)
    ->action(function ( $fileId, $read, $write ) use ($parser) {
        /** @var string $fileId */
        /** @var array $read */
        /** @var array $write */

        $client = new Client();
        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}');
        $params = [];
        /** Body Params */
        $params['read'] = !is_array($read) ? array($read) : $read;
        $params['write'] = !is_array($write) ? array($write) : $write;
        $response =  $client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('deleteFile')
    ->label('description', "Delete a file by its unique ID. Only users with write permissions have access to delete this resource.\n\n")
    ->param('fileId', '' , new Wildcard() , 'File unique ID.',  false)
    ->action(function ( $fileId ) use ($parser) {
        /** @var string $fileId */

        $client = new Client();
        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}');
        $params = [];
        $response =  $client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getFileDownload')
    ->label('description', "Get a file content by its unique ID. The endpoint response return with a 'Content-Disposition: attachment' header that tells the browser to start downloading the file to user downloads directory.\n\n")
    ->param('fileId', '' , new Wildcard() , 'File unique ID.',  false)
    ->action(function ( $fileId ) use ($parser) {
        /** @var string $fileId */

        $client = new Client();
        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}/download');
        $params = [];
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getFilePreview')
    ->label('description', "Get a file preview image. Currently, this method supports preview for image files (jpg, png, and gif), other supported formats, like pdf, docs, slides, and spreadsheets, will return the file icon image. You can also pass query string arguments for cutting and resizing your preview image.\n\n")
    ->param('fileId', '' , new Wildcard() , 'File unique ID',  false)
    ->param('width', 0 , new Wildcard() , 'Resize preview image width, Pass an integer between 0 to 4000.',  true)
    ->param('height', 0 , new Wildcard() , 'Resize preview image height, Pass an integer between 0 to 4000.',  true)
    ->param('gravity', 'center' , new Wildcard() , 'Image crop gravity. Can be one of center,top-left,top,top-right,left,right,bottom-left,bottom,bottom-right',  true)
    ->param('quality', 100 , new Wildcard() , 'Preview image quality. Pass an integer between 0 to 100. Defaults to 100.',  true)
    ->param('borderWidth', 0 , new Wildcard() , 'Preview image border in pixels. Pass an integer between 0 to 100. Defaults to 0.',  true)
    ->param('borderColor', '' , new Wildcard() , 'Preview image border color. Use a valid HEX color, no # is needed for prefix.',  true)
    ->param('borderRadius', 0 , new Wildcard() , 'Preview image border radius in pixels. Pass an integer between 0 to 4000.',  true)
    ->param('opacity', 1 , new Wildcard() , 'Preview image opacity. Only works with images having an alpha channel (like png). Pass a number between 0 to 1.',  true)
    ->param('rotation', 0 , new Wildcard() , 'Preview image rotation in degrees. Pass an integer between 0 and 360.',  true)
    ->param('background', '' , new Wildcard() , 'Preview image background color. Only works with transparent images (png). Use a valid HEX color, no # is needed for prefix.',  true)
    ->param('output', '' , new Wildcard() , 'Output format type (jpeg, jpg, png, gif and webp).',  true)
    ->action(function ( $fileId, $width, $height, $gravity, $quality, $borderWidth, $borderColor, $borderRadius, $opacity, $rotation, $background, $output ) use ($parser) {
        /** @var string $fileId */
        /** @var integer $width */
        /** @var integer $height */
        /** @var string $gravity */
        /** @var integer $quality */
        /** @var integer $borderWidth */
        /** @var string $borderColor */
        /** @var integer $borderRadius */
        /** @var number $opacity */
        /** @var integer $rotation */
        /** @var string $background */
        /** @var string $output */

        $client = new Client();
        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}/preview');
        $params = [];
        /** Query Params */
        $params['width'] = $width;
        $params['height'] = $height;
        $params['gravity'] = $gravity;
        $params['quality'] = $quality;
        $params['borderWidth'] = $borderWidth;
        $params['borderColor'] = $borderColor;
        $params['borderRadius'] = $borderRadius;
        $params['opacity'] = $opacity;
        $params['rotation'] = $rotation;
        $params['background'] = $background;
        $params['output'] = $output;
        $params['project'] = $client->getPreference('X-Appwrite-Project');
        $params['key'] = $client->getPreference('X-Appwrite-Key');
        $path = $client->getPreference(Client::PREFERENCE_ENDPOINT).$path . "?" . http_build_query($params);
        Console::success($path);
    });

$cli
    ->task('getFileView')
    ->label('description', "Get a file content by its unique ID. This endpoint is similar to the download method but returns with no  'Content-Disposition: attachment' header.\n\n")
    ->param('fileId', '' , new Wildcard() , 'File unique ID.',  false)
    ->action(function ( $fileId ) use ($parser) {
        /** @var string $fileId */

        $client = new Client();
        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}/view');
        $params = [];
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
        Console::log("\nUsage : appwrite storage [COMMAND]\n");
        Console::log("Commands :");
        $commands = [
                "listFiles" => "Get a list of all the user files. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project's files. [Learn more about different API modes](/docs/admin).",
                "createFile" => "Create a new file. The user who creates the file will automatically be assigned to read and write access unless he has passed custom values for read and write arguments.",
                "getFile" => "Get a file by its unique ID. This endpoint response returns a JSON object with the file metadata.",
                "updateFile" => "Update a file by its unique ID. Only users with write permissions have access to update this resource.",
                "deleteFile" => "Delete a file by its unique ID. Only users with write permissions have access to delete this resource.",
                "getFileDownload" => "Get a file content by its unique ID. The endpoint response return with a 'Content-Disposition: attachment' header that tells the browser to start downloading the file to user downloads directory.",
                "getFilePreview" => "Get a file preview image. Currently, this method supports preview for image files (jpg, png, and gif), other supported formats, like pdf, docs, slides, and spreadsheets, will return the file icon image. You can also pass query string arguments for cutting and resizing your preview image.",
                "getFileView" => "Get a file content by its unique ID. This endpoint is similar to the download method but returns with no  'Content-Disposition: attachment' header.",
        ];
        $parser->formatArray($commands);
        Console::log("\nRun 'appwrite storage COMMAND --help' for more information on a command.");
    });


$cli->run();
