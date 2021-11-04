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
            Console::log("\nUsage : appwrite database {$taskName} --[OPTIONS] \n");
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
    ->task('listCollections')
    ->label('description', "Get a list of all the user collections. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project's collections. [Learn more about different API modes](/docs/admin).\n\n")
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
        $path   = str_replace([], [], '/database/collections');
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
    ->task('createCollection')
    ->label('description', "Create a new Collection.\n\n")
    ->param('name', '' , new Wildcard() , 'Collection name. Max length: 128 chars.',  false)
    ->param('read', '' , new Wildcard() , 'An array of strings with read permissions. By default no user is granted with any read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  false)
    ->param('write', '' , new Wildcard() , 'An array of strings with write permissions. By default no user is granted with any write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  false)
    ->param('rules', '' , new Wildcard() , 'Array of [rule objects](/docs/rules). Each rule define a collection field name, data type and validation.',  false)
    ->action(function ( $name, $read, $write, $rules ) use ($parser) {
        /** @var string $name */
        /** @var array $read */
        /** @var array $write */
        /** @var array $rules */

        $client = new Client();
        $path   = str_replace([], [], '/database/collections');
        $params = [];
        /** Body Params */
        $params['name'] = $name;
        $params['read'] = !is_array($read) ? array($read) : $read;
        $params['write'] = !is_array($write) ? array($write) : $write;
        $params['rules'] = !is_array($rules) ? array($rules) : $rules;
        $response =  $client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getCollection')
    ->label('description', "Get a collection by its unique ID. This endpoint response returns a JSON object with the collection metadata.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID.',  false)
    ->action(function ( $collectionId ) use ($parser) {
        /** @var string $collectionId */

        $client = new Client();
        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateCollection')
    ->label('description', "Update a collection by its unique ID.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID.',  false)
    ->param('name', '' , new Wildcard() , 'Collection name. Max length: 128 chars.',  false)
    ->param('read', [] , new Wildcard() , 'An array of strings with read permissions. By default inherits the existing read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->param('write', [] , new Wildcard() , 'An array of strings with write permissions. By default inherits the existing write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->param('rules', [] , new Wildcard() , 'Array of [rule objects](/docs/rules). Each rule define a collection field name, data type and validation.',  true)
    ->action(function ( $collectionId, $name, $read, $write, $rules ) use ($parser) {
        /** @var string $collectionId */
        /** @var string $name */
        /** @var array $read */
        /** @var array $write */
        /** @var array $rules */

        $client = new Client();
        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}');
        $params = [];
        /** Body Params */
        $params['name'] = $name;
        $params['read'] = !is_array($read) ? array($read) : $read;
        $params['write'] = !is_array($write) ? array($write) : $write;
        $params['rules'] = !is_array($rules) ? array($rules) : $rules;
        $response =  $client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('deleteCollection')
    ->label('description', "Delete a collection by its unique ID. Only users with write permissions have access to delete this resource.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID.',  false)
    ->action(function ( $collectionId ) use ($parser) {
        /** @var string $collectionId */

        $client = new Client();
        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}');
        $params = [];
        $response =  $client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('listDocuments')
    ->label('description', "Get a list of all the user documents. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project's documents. [Learn more about different API modes](/docs/admin).\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection).',  false)
    ->param('filters', [] , new Wildcard() , 'Array of filter strings. Each filter is constructed from a key name, comparison operator (=, !=, &gt;, &lt;, &lt;=, &gt;=) and a value. You can also use a dot (.) separator in attribute names to filter by child document attributes. Examples: &#039;name=John Doe&#039; or &#039;category.$id&gt;=5bed2d152c362&#039;.',  true)
    ->param('limit', 25 , new Wildcard() , 'Maximum number of documents to return in response.  Use this value to manage pagination. By default will return maximum 25 results. Maximum of 100 results allowed per request.',  true)
    ->param('offset', 0 , new Wildcard() , 'Offset value. The default value is 0. Use this param to manage pagination.',  true)
    ->param('orderField', '' , new Wildcard() , 'Document field that results will be sorted by.',  true)
    ->param('orderType', 'ASC' , new Wildcard() , 'Order direction. Possible values are DESC for descending order, or ASC for ascending order.',  true)
    ->param('orderCast', 'string' , new Wildcard() , 'Order field type casting. Possible values are int, string, date, time or datetime. The database will attempt to cast the order field to the value you pass here. The default value is a string.',  true)
    ->param('search', '' , new Wildcard() , 'Search query. Enter any free text search. The database will try to find a match against all document attributes and children. Max length: 256 chars.',  true)
    ->action(function ( $collectionId, $filters, $limit, $offset, $orderField, $orderType, $orderCast, $search ) use ($parser) {
        /** @var string $collectionId */
        /** @var array $filters */
        /** @var integer $limit */
        /** @var integer $offset */
        /** @var string $orderField */
        /** @var string $orderType */
        /** @var string $orderCast */
        /** @var string $search */

        $client = new Client();
        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}/documents');
        $params = [];
        /** Query Params */
        $params['filters'] = !is_array($filters) ? array($filters) : $filters;
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        $params['orderField'] = $orderField;
        $params['orderType'] = $orderType;
        $params['orderCast'] = $orderCast;
        $params['search'] = $search;
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('createDocument')
    ->label('description', "Create a new Document. Before using this route, you should create a new collection resource using either a [server integration](/docs/server/database#databaseCreateCollection) API or directly from your database console.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection).',  false)
    ->param('data', '' , new Wildcard() , 'Document data as JSON object.',  false)
    ->param('read', [] , new Wildcard() , 'An array of strings with read permissions. By default only the current user is granted with read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->param('write', [] , new Wildcard() , 'An array of strings with write permissions. By default only the current user is granted with write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->param('parentDocument', '' , new Wildcard() , 'Parent document unique ID. Use when you want your new document to be a child of a parent document.',  true)
    ->param('parentProperty', '' , new Wildcard() , 'Parent document property name. Use when you want your new document to be a child of a parent document.',  true)
    ->param('parentPropertyType', 'assign' , new Wildcard() , 'Parent document property connection type. You can set this value to **assign**, **append** or **prepend**, default value is assign. Use when you want your new document to be a child of a parent document.',  true)
    ->action(function ( $collectionId, $data, $read, $write, $parentDocument, $parentProperty, $parentPropertyType ) use ($parser) {
        /** @var string $collectionId */
        /** @var object $data */
        /** @var array $read */
        /** @var array $write */
        /** @var string $parentDocument */
        /** @var string $parentProperty */
        /** @var string $parentPropertyType */

        $client = new Client();
        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}/documents');
        $params = [];
        /** Body Params */
        $params['data'] = \json_decode($data);
        $params['read'] = !is_array($read) ? array($read) : $read;
        $params['write'] = !is_array($write) ? array($write) : $write;
        $params['parentDocument'] = $parentDocument;
        $params['parentProperty'] = $parentProperty;
        $params['parentPropertyType'] = $parentPropertyType;
        $response =  $client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('getDocument')
    ->label('description', "Get a document by its unique ID. This endpoint response returns a JSON object with the document data.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection).',  false)
    ->param('documentId', '' , new Wildcard() , 'Document unique ID.',  false)
    ->action(function ( $collectionId, $documentId ) use ($parser) {
        /** @var string $collectionId */
        /** @var string $documentId */

        $client = new Client();
        $path   = str_replace(['{collectionId}', '{documentId}'], [$collectionId, $documentId], '/database/collections/{collectionId}/documents/{documentId}');
        $params = [];
        $response =  $client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('updateDocument')
    ->label('description', "Update a document by its unique ID. Using the patch method you can pass only specific fields that will get updated.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection).',  false)
    ->param('documentId', '' , new Wildcard() , 'Document unique ID.',  false)
    ->param('data', '' , new Wildcard() , 'Document data as JSON object.',  false)
    ->param('read', [] , new Wildcard() , 'An array of strings with read permissions. By default inherits the existing read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->param('write', [] , new Wildcard() , 'An array of strings with write permissions. By default inherits the existing write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions.',  true)
    ->action(function ( $collectionId, $documentId, $data, $read, $write ) use ($parser) {
        /** @var string $collectionId */
        /** @var string $documentId */
        /** @var object $data */
        /** @var array $read */
        /** @var array $write */

        $client = new Client();
        $path   = str_replace(['{collectionId}', '{documentId}'], [$collectionId, $documentId], '/database/collections/{collectionId}/documents/{documentId}');
        $params = [];
        /** Body Params */
        $params['data'] = \json_decode($data);
        $params['read'] = !is_array($read) ? array($read) : $read;
        $params['write'] = !is_array($write) ? array($write) : $write;
        $response =  $client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
        $parser->parseResponse($response);
    });

$cli
    ->task('deleteDocument')
    ->label('description', "Delete a document by its unique ID. This endpoint deletes only the parent documents, its attributes and relations to other documents. Child documents **will not** be deleted.\n\n")
    ->param('collectionId', '' , new Wildcard() , 'Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection).',  false)
    ->param('documentId', '' , new Wildcard() , 'Document unique ID.',  false)
    ->action(function ( $collectionId, $documentId ) use ($parser) {
        /** @var string $collectionId */
        /** @var string $documentId */

        $client = new Client();
        $path   = str_replace(['{collectionId}', '{documentId}'], [$collectionId, $documentId], '/database/collections/{collectionId}/documents/{documentId}');
        $params = [];
        $response =  $client->call(Client::METHOD_DELETE, $path, [
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
        Console::log("\nUsage : appwrite database [COMMAND]\n");
        Console::log("Commands :");
        $commands = [
                "listCollections" => "Get a list of all the user collections. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project's collections. [Learn more about different API modes](/docs/admin).",
                "createCollection" => "Create a new Collection.",
                "getCollection" => "Get a collection by its unique ID. This endpoint response returns a JSON object with the collection metadata.",
                "updateCollection" => "Update a collection by its unique ID.",
                "deleteCollection" => "Delete a collection by its unique ID. Only users with write permissions have access to delete this resource.",
                "listDocuments" => "Get a list of all the user documents. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project's documents. [Learn more about different API modes](/docs/admin).",
                "createDocument" => "Create a new Document. Before using this route, you should create a new collection resource using either a [server integration](/docs/server/database#databaseCreateCollection) API or directly from your database console.",
                "getDocument" => "Get a document by its unique ID. This endpoint response returns a JSON object with the document data.",
                "updateDocument" => "Update a document by its unique ID. Using the patch method you can pass only specific fields that will get updated.",
                "deleteDocument" => "Delete a document by its unique ID. This endpoint deletes only the parent documents, its attributes and relations to other documents. Child documents **will not** be deleted.",
        ];
        $parser->formatArray($commands);
        Console::log("\nRun 'appwrite database COMMAND --help' for more information on a command.");
    });


$cli->run();
