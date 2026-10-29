<?php

declare(strict_types=1);

namespace Tests\E2E;

final class Output
{
    public const array PING_RESPONSE = [
        'GET:/v1/ping:passed',
    ];

    public const array FOO_RESPONSES = [
        'GET:/v1/mock/tests/foo:passed',
        'POST:/v1/mock/tests/foo:passed',
        'PUT:/v1/mock/tests/foo:passed',
        'PATCH:/v1/mock/tests/foo:passed',
        'DELETE:/v1/mock/tests/foo:passed',
    ];

    public const array BAR_RESPONSES = [
        'GET:/v1/mock/tests/bar:passed',
        'POST:/v1/mock/tests/bar:passed',
        'PUT:/v1/mock/tests/bar:passed',
        'PATCH:/v1/mock/tests/bar:passed',
        'DELETE:/v1/mock/tests/bar:passed',
    ];

    public const array GENERAL_RESPONSES = [
        'GET:/v1/mock/tests/general/redirect/done:passed',
    ];

    // Server SDKs answer a redirect with an error instead of following it: the next location may
    // be another host, and the request carries the API key. Printed as `code:message`.
    public const array SERVER_REDIRECT_RESPONSES = [
        '301:Redirect to /v1/mock/tests/general/redirect/done not followed',
    ];

    // Server SDKs keep no cookies: the cookie set-cookie answers with is not sent with get-cookie,
    // so one caller's session cannot reach the next call made on the same client.
    public const array SERVER_COOKIE_RESPONSES = [
        'GET:/v1/mock/tests/general/set-cookie:passed',
        'Missing cookie value',
    ];

    public const array LOCATION_RESPONSES = [
        'GET:/v1/mock/tests/general/download:passed',
        'GET:/v1/mock/tests/general/download:passed:as:impersonated',
    ];

    public const array PATH_PARAM_RESPONSES = [
        'GET:/v1/mock/tests/general/path/grant%2Fspecial%26id:passed',
    ];

    // getPath('../grant?special=id#x y%') and getPath('unique()'): every reserved character
    // stays inside the one segment, and the parentheses of unique() are left as they are.
    public const array PATH_SEGMENT_RESPONSES = [
        'GET:/v1/mock/tests/general/path/..%2Fgrant%3Fspecial%3Did%23x%20y%25:passed',
        'GET:/v1/mock/tests/general/path/unique():passed',
    ];

    public const array PATH_DOT_RESPONSES = [
        'Path parameter cannot be "." or ".."',
        'Path parameter cannot be "." or ".."',
    ];

    // One behavioral contract, invoked by each SDK's language adapter.
    // The mock never emits these errors: reaching it cannot pass the rejection checks.
    public const array PATH_VALIDATION_RESPONSES = [
        'Missing required parameter: "id"',
        'Missing required parameter: "plain"',
        'GET:/v1/mock/tests/general/path-validation/0/0:passed',
    ];

    // Dart/Flutter, Kotlin/Android, Swift/Apple and Rust keep a null path
    // segment as literal "null", never as an empty segment.
    public const array NULL_PATH_RESPONSE = [
        'GET:/v1/mock/tests/general/path-validation/null/0:passed',
    ];

    // Query values containing `&`, `+` and `#` reach the server unchanged.
    public const array QUERY_ENCODING_RESPONSES = [
        'GET:/v1/mock/tests/general/query:passed',
    ];

    public const array OAUTH_RESPONSES = [
        'https://localhost?code=abcdef&state=123456',
    ];

    public const array DOWNLOAD_RESPONSES = [
        'GET:/v1/mock/tests/general/download:passed',
    ];

    public const array COOKIE_RESPONSES = [
        'GET:/v1/mock/tests/general/set-cookie:passed',
        'GET:/v1/mock/tests/general/get-cookie:passed',
    ];

    public const array ENUM_RESPONSES = [
        'POST:/v1/mock/tests/general/enum:passed',
    ];

    public const array MODEL_RESPONSES = [
        'POST:/v1/mock/tests/general/models:passed',
        'POST:/v1/mock/tests/general/models/array:passed',
    ];

    public const array MODEL_DEFAULT_RESPONSES = [
        'preset: name=guest, count=25, enabled=false',
        'preset: name=guest, count=25, enabled=false',
        'preset: name=server, count=25, enabled=true',
        'empty presets: 0',
    ];

    public const array JS_MODEL_TO_STRING_RESPONSES = [
        'preset toString: {"name":"guest","count":25,"enabled":false,"tier":"free","tags":[],"settings":{},"verified":null,"scopes":["read","write"]}',
    ];

    public const array JS_DEFAULT_COPY_RESPONSES = [
        'preset tags shared: 0',
    ];

    public const array JS_NESTED_BUILDER_RESPONSES = [
        'nested builder: passed',
    ];

    public const array NULLABLE_PARAM_RESPONSES = [
        'POST:/v1/mock/tests/general/nullable:passed',
    ];

    public const array NULLABLE_MODEL_RESPONSES = [
        'preset verified: null',
    ];

    public const array ENUM_DEFAULT_RESPONSES = [
        'preset quality default: medium',
    ];

    public const array ENUM_ARRAY_MODEL_RESPONSES = [
        'preset scopes: read,write',
    ];

    public const array PYTHON_MODEL_DEFAULT_RESPONSES = [
        'preset tier: PresetTier.FREE',
    ];

    public const array DOTNET_DATETIME_STRING_RESPONSES = [
        'datetime property: 2026-10-16T22:10:08.123+00:00',
        'datetime data: 2026-10-16T22:10:08.123+00:00',
    ];

    public const array MAP_MODEL_DEFAULT_RESPONSES = [
        'preset settings: options=0',
    ];

    public const array NESTED_MODEL_DEFAULT_RESPONSES = [
        'preset profile: prefs=0',
    ];

    public const array OBJECT_ARRAY_RESPONSES = [
        'POST:/v1/mock/tests/general/documents:passed',
    ];

    public const array OPTIONAL_PARAM_RESPONSES = [
        'width=-1,height=128,name=omitted',
        'width=0,height=64,name=zero',
    ];

    public const array UNION_RESPONSES = [
        'GET:/v1/mock/tests/union:passed',
        'test-data',
        'stub',
    ];

    public const array OPTIONAL_UPLOAD_RESPONSES = [
        'optional-upload:without-file',
        'optional-upload:without-file',
        'optional-upload:without-file',
        'optional-upload:without-file',
        'optional-upload:with-file',
        'optional-upload:with-file',
        'required-file:rejected',
    ];

    public const array MULTIPART_OBJECT_RESPONSES = [
        'optional-upload:without-file:with-metadata',
        'optional-upload:with-file:with-metadata',
    ];

    public const array OPTIONAL_ATTACHMENT_RESPONSES = [
        'optional-upload:without-file',
        'optional-upload:with-file',
    ];

    public const array TEXT_RESPONSES = [
        '; café zone',
        'www 3600 IN A 192.0.2.1',
        '',
        'mixed-model',
    ];

    public const array TEXT_UPLOAD_RESPONSES = [
        'zone-import:without-file',
        '',
        'zone-import:with-file',
        '',
    ];

    public const array UPLOAD_RESPONSE = [
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    public const array UPLOAD_RESPONSES = [
        'POST:/v1/mock/tests/general/upload:passed',
        'POST:/v1/mock/tests/general/upload:passed',
        'POST:/v1/mock/tests/general/upload:passed',
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    public const array GENERIC_UPLOAD_RESPONSES = [
        'PUT:/v1/mock/tests/general/upload-generic:passed',
    ];

    public const array BOUNDARY_UPLOAD_RESPONSES = [
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    public const array UPLOAD_WITH_ID_RESPONSES = [
        'POST:/v1/mock/tests/general/upload-with-id:passed',
    ];

    public const array LARGE_FILE_RESPONSES = [
        'POST:/v1/mock/tests/general/upload:passed',
    ];

    /**
     * 'Mock 400 error'                              -> message
     * '{"message":"Mock 400 error","code":400}'     -> response
     */
    public const array EXCEPTION_RESPONSES = [
        'Mock 400 error',
        '{"message":"Mock 400 error","code":400}',
        'Mock 500 error',
        '{"message":"Mock 500 error","code":500}',
        'This is a text error',
        'This is a text error',
    ];

    public const array ENDPOINT_RESPONSES = [
        'Invalid endpoint URL: htp://cloud.appwrite.io/v1',
    ];

    public const array REALTIME_RESPONSES = [
        'WS:/v1/realtime:passed',
        'WS:/v1/realtime:passed',
        'Realtime failed!',
        'Realtime error:passed',
        'Realtime unsubscribe:passed',
        'Realtime update:passed',
        'Realtime presence:passed',
        'Realtime disconnect:passed',
    ];

    // Native push (MQTT) round-trip against the mock broker: subscribe, receive, and the
    // connection lifecycle hook (onOpen) firing.
    public const array PUSH_RESPONSES = [
        'Push subscribe:passed',
        'Push open:passed',
        'Push message:passed',
        'Push qos:passed',
        'Push user topic:passed',
        'Push user session topic:passed',
        'Push user no credential:passed',
    ];

    // Web and React Native derive their client id themselves (mqtt.js needs one): a user signing
    // in again with a new session still gets what was missed in between replayed.
    public const array PUSH_SESSION_REPLAY_RESPONSES = [
        'Push session replay:passed',
    ];

    public const array PUSH_CREDENTIAL_SWITCH_RESPONSES = [
        'Push credential switch:passed',
        'Push credential pending switch:passed',
    ];

    public const array PUSH_CLOSE_WHILE_CONNECTING_RESPONSES = [
        'Push close while connecting:passed',
    ];

    public const array PUSH_CLOSE_RIGHT_AFTER_SUBSCRIBE_RESPONSES = [
        'Push close right after subscribe:passed',
    ];

    public const array PUSH_CLOSE_FROM_CALLBACK_RESPONSES = [
        'Push close from callback:passed',
    ];

    public const array WEB_PUSH_CONNECTION_RESPONSES = [
        'Push concurrent refused:passed',
        'Push tab client id:passed',
        'Push notification opened web:passed',
    ];

    // Android, Apple, Flutter and Web: a topic-less subscribe uses the session the client signed
    // in with when no JWT or session is set on it.
    public const array PUSH_SIGN_IN_SESSION_RESPONSES = [
        'Push user sign-in session topic:passed',
    ];

    public const array PUSH_SESSION_COOKIE_RESPONSES = [
        'Push user cookie topic:passed',
        'Push user cookie switch:passed',
        'Push user cookie pending switch:passed',
    ];

    public const array PUSH_NATIVE_SESSION_COOKIE_RESPONSES = [
        'Push session cookie:passed',
    ];

    // Broker errors reaching onError: a refused CONNECT and a server DISCONNECT, carrying the
    // broker's MQTT 5 reason string (Apple checks only that the error arrives: MQTTNIO does not
    // expose the reason string).
    public const array PUSH_ERROR_RESPONSES = [
        'Push connect error:passed',
        'Push disconnect error:passed',
    ];

    // Android background delivery used the way an app does: a background subscription's
    // message, the scheduled wake-up after the process died bringing the next message to the
    // app's PushReceiver and a notification, sign-out stopping it, and a refused credential
    // stopping it with onError.
    // Flutter's public Push API on the Android path, with a stand-in for the native plugin on its
    // channels: background subscribe completes once the plugin reports it subscribed, a message
    // reaches the callback, and after sign-out it no longer reaches the app.
    public const array FLUTTER_PUSH_NATIVE_RESPONSES = [
        'Push native subscribe:passed',
        'Push native message:passed',
        'Push native close:passed',
    ];

    // Android: a saved background subscription the app later subscribes to with background off,
    // then unsubscribes, delivers nothing after a restart.
    public const array ANDROID_PUSH_OPT_OUT_RESPONSES = [
        'Push background opt-out:passed',
    ];

    // Android: unsubscribing one of two in-process subscriptions on the same topic stops its
    // callback, though the other subscription keeps the broker filter.
    public const array ANDROID_PUSH_SHARED_TOPIC_RESPONSES = [
        'Push shared topic unsubscribe:passed',
    ];

    public const array ANDROID_COOKIE_STORE_RESPONSES = [
        'Cookie store reload:passed',
        'Cookie store saved format:passed',
    ];

    // React Native and Flutter on Android: when a Push with another credential starts background
    // delivery, the Push that had it hears on onError that its background delivery stopped.
    public const array PUSH_NATIVE_DISPLACED_RESPONSES = [
        'Push native displaced:passed',
    ];

    // React Native and Flutter on Android ask for POST_NOTIFICATIONS once background delivery starts.
    public const array PUSH_NOTIFICATION_PERMISSION_RESPONSES = [
        'Push notification permission:passed',
    ];

    public const array REACT_NATIVE_PUSH_JS_RESPONSES = [
        'Push native connection JS:passed',
        'Push background status JS:passed',
        'Push notification opened JS:passed',
        'Push notification opened expo:passed',
        'Push notification content JS:passed',
    ];

    // React Native on Android: the tap that launched the app is reported once, later taps as events.
    public const array PUSH_NOTIFICATION_OPENED_RESPONSES = [
        'Push notification opened:passed',
    ];

    // React Native on Android: background runs follow the session in the WebView cookie store.
    public const array REACT_NATIVE_PUSH_COOKIE_REFRESH_RESPONSES = [
        'Push background cookie refresh:passed',
        'Push background reconnect credential:passed',
        'Push background drain:passed',
    ];

    // Android only: no notification while the app is on screen, the watchdog job, resume with the
    // current credential, and restricted background usage.
    public const array ANDROID_PUSH_BACKGROUND_RESPONSES = [
        'Push background message:passed',
        'Push foreground no notification:passed',
        'Push background watchdog:passed',
        'Push background restore:passed',
        'Push background resume credential:passed',
        'Push background close:passed',
        'Push background refused:passed',
        'Push background restricted:passed',
    ];

    public const array PUSH_BACKGROUND_RESPONSES = [
        'Push background message:passed',
        'Push background restore:passed',
        'Push background close:passed',
        'Push background refused:passed',
    ];

    public const array ANDROID_PUSH_NOTIFICATION_RESPONSES = [
        'Push notification content:passed',
        'Push notification opened:passed',
    ];

    public const array QUERY_HELPER_RESPONSES = [
        '{"method":"equal","attribute":"released","values":[true]}',
        '{"method":"equal","attribute":"title","values":["Spiderman","Dr. Strange"]}',
        '{"method":"notEqual","attribute":"title","values":["Spiderman"]}',
        '{"method":"lessThan","attribute":"releasedYear","values":[1990]}',
        '{"method":"greaterThan","attribute":"releasedYear","values":[1990]}',
        '{"method":"search","attribute":"name","values":["john"]}',
        '{"method":"isNull","attribute":"name"}',
        '{"method":"isNotNull","attribute":"name"}',
        '{"method":"between","attribute":"age","values":[50,100]}',
        '{"method":"between","attribute":"age","values":[50.5,100.5]}',
        '{"method":"between","attribute":"name","values":["Anna","Brad"]}',
        '{"method":"startsWith","attribute":"name","values":["Ann"]}',
        '{"method":"endsWith","attribute":"name","values":["nne"]}',
        '{"method":"select","values":["name","age"]}',
        '{"method":"orderAsc","attribute":"title"}',
        '{"method":"orderDesc","attribute":"title"}',
        '{"method":"orderRandom"}',
        '{"method":"cursorAfter","values":["my_movie_id"]}',
        '{"method":"cursorBefore","values":["my_movie_id"]}',
        '{"method":"limit","values":[50]}',
        '{"method":"offset","values":[20]}',
        '{"method":"contains","attribute":"title","values":["Spider"]}',
        '{"method":"contains","attribute":"labels","values":["first"]}',
        '{"method":"containsAny","attribute":"labels","values":["first","second"]}',
        '{"method":"containsAll","attribute":"labels","values":["first","second"]}',
        '{"method":"notContains","attribute":"title","values":["Spider"]}',
        '{"method":"notSearch","attribute":"name","values":["john"]}',
        '{"method":"notBetween","attribute":"age","values":[50,100]}',
        '{"method":"notStartsWith","attribute":"name","values":["Ann"]}',
        '{"method":"notEndsWith","attribute":"name","values":["nne"]}',
        '{"method":"lessThan","attribute":"$createdAt","values":["2023-01-01"]}',
        '{"method":"greaterThan","attribute":"$createdAt","values":["2023-01-01"]}',
        '{"method":"between","attribute":"$createdAt","values":["2023-01-01","2023-12-31"]}',
        '{"method":"lessThan","attribute":"$updatedAt","values":["2023-01-01"]}',
        '{"method":"greaterThan","attribute":"$updatedAt","values":["2023-01-01"]}',
        '{"method":"between","attribute":"$updatedAt","values":["2023-01-01","2023-12-31"]}',
        '{"method":"distanceEqual","attribute":"location","values":[[[[40.7128,-74],[40.7128,-74]],1000,true]]}',
        '{"method":"distanceEqual","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceNotEqual","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceNotEqual","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceGreaterThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceGreaterThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceLessThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"distanceLessThan","attribute":"location","values":[[[40.7128,-74],1000,true]]}',
        '{"method":"vectorDot","attribute":"embedding","values":[[0.1,0.2,0.3]]}',
        '{"method":"vectorCosine","attribute":"embedding","values":[[0.1,0.2,0.3]]}',
        '{"method":"vectorEuclidean","attribute":"embedding","values":[[0.1,0.2,0.3]]}',
        '{"method":"intersects","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notIntersects","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"crosses","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notCrosses","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"overlaps","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notOverlaps","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"touches","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"notTouches","attribute":"location","values":[[40.7128,-74]]}',
        '{"method":"contains","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"notContains","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"equal","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"notEqual","attribute":"location","values":[[40.7128,-74],[40.7128,-74]]}',
        '{"method":"or","values":[{"method":"equal","attribute":"released","values":[true]},{"method":"lessThan","attribute":"releasedYear","values":[1990]}]}',
        '{"method":"and","values":[{"method":"equal","attribute":"released","values":[false]},{"method":"greaterThan","attribute":"releasedYear","values":[2015]}]}',
        '{"method":"regex","attribute":"name","values":["pattern.*"]}',
        '{"method":"exists","values":["attr1","attr2"]}',
        '{"method":"notExists","values":["attr1","attr2"]}',
        '{"method":"elemMatch","attribute":"friends","values":[{"method":"equal","attribute":"name","values":["Alice"]},{"method":"greaterThan","attribute":"age","values":[18]}]}',
        '{"method":"relationship","attribute":"comments","values":[{"method":"equal","attribute":"approved","values":[true]},{"method":"limit","values":[2]}]}',
    ];

    // Each language harness sends these through a real listRows request and prints
    // the echo the mock server returns, so the assertion reads the request the
    // server received rather than what a helper rendered in process.
    public const string QUERY_TRANSPORT_RESPONSE = '__QUERY_TRANSPORT_RESPONSE__';

    public const array QUERY_TRANSPORT_RESPONSES = [
        self::QUERY_TRANSPORT_RESPONSE,
    ];

    public const array QUERY_TRANSPORT_QUERIES = [
        '{"method":"count","attribute":"*","values":["total"]}',
        '{"method":"join","attribute":"orders","values":["$id","=","customerId"]}',
        '{"method":"groupBy","values":["status"]}',
        '{"method":"distinct"}',
        '{"method":"covers","attribute":"location","values":[[1,2]]}',
        '{"method":"countDistinct","attribute":"year","values":["uniqueYears"]}',
        '{"method":"sum","attribute":"price","values":["total"]}',
        '{"method":"avg","attribute":"price","values":["avgPrice"]}',
        '{"method":"min","attribute":"price","values":["lowest"]}',
        '{"method":"max","attribute":"price","values":["highest"]}',
        '{"method":"stddev","attribute":"price","values":["sd"]}',
        '{"method":"stddevPop","attribute":"price","values":["sdp"]}',
        '{"method":"stddevSamp","attribute":"price","values":["sds"]}',
        '{"method":"variance","attribute":"price","values":["var"]}',
        '{"method":"varPop","attribute":"price","values":["vp"]}',
        '{"method":"varSamp","attribute":"price","values":["vs"]}',
        '{"method":"bitAnd","attribute":"flags","values":["band"]}',
        '{"method":"bitOr","attribute":"flags","values":["bor"]}',
        '{"method":"bitXor","attribute":"flags","values":["bxor"]}',
        '{"method":"having","values":[{"method":"greaterThan","attribute":"total","values":[1]}]}',
        '{"method":"leftJoin","attribute":"orders","values":["$id","=","customerId","ord"]}',
        '{"method":"rightJoin","attribute":"orders","values":["$id","=","customerId"]}',
        '{"method":"fullOuterJoin","attribute":"orders","values":["$id","=","customerId"]}',
        '{"method":"crossJoin","attribute":"orders","values":["ord"]}',
        '{"method":"on","values":["$id","=","customerId"]}',
        '{"method":"leftJoin","attribute":"orders","values":["ord",{"method":"on","values":["$id","=","customerId"]},{"method":"equal","attribute":"ord.status","values":["paid"]}]}',
        '{"method":"notCovers","attribute":"location","values":[[1,2]]}',
        '{"method":"spatialEquals","attribute":"location","values":[[1,2]]}',
        '{"method":"notSpatialEquals","attribute":"location","values":[[1,2]]}',
        '{"method":"limit","values":[10]}',
        '{"method":"offset","values":[10]}',
        '{"method":"limit","values":[1]}',
    ];

    public const array PERMISSION_HELPER_RESPONSES = [
        'read("any")',
        'write("user:userid")',
        'create("users")',
        'update("guests")',
        'delete("team:teamId/owner")',
        'delete("team:teamId")',
        'create("member:memberId")',
        'update("users/verified")',
        'update("user:userid/unverified")',
        'create("label:admin")',
    ];

    public const array ID_HELPER_RESPONSES = [
        'unique()',
        'custom_id'
    ];

    public const array ANDROID_JAVA_INTEROP_RESPONSES = [
        'databases.db1',
        'account',
    ];

    public const array JAVA_INTEROP_RESPONSES = [
        'unique()',
        'read("any")',
        '{"method":"limit","values":[1]}',
        '{"method":"count","attribute":"*"}',
        '{"method":"increment","values":[1]}',
        '{"method":"increment","values":[5]}',
        'Missing required parameter: "id"',
        'PUT:/v1/mock/tests/general/upload-generic:passed',
    ];

    public const array TOPIC_HELPER_RESPONSES = [
        'user/123/notification',
        'org/42/user/123/notification',
        'Topic empty path:passed',
        'Topic empty level:passed',
        'Topic slash:passed',
        'Topic plus:passed',
        'Topic hash:passed',
        'Topic nul:passed',
    ];

    public const array QUERY_PAGE_OVERFLOW_RESPONSES = [
        'Query page overflow:passed',
    ];

    public const array UNITY_MANAGER_RESPONSES = [
        'Manager push reinitialize:passed',
        'Config all services:passed',
        'Config some services:passed',
    ];

    public const array ADDITIONAL_PROPERTIES_RESPONSES = [
        '{"theme":"dark","timezone":"UTC"}',
        '{"$id":"row1","custom":"value","nested":{"enabled":true}}',
        '{"data":{"enabled":true},"status":"ok","extra":"kept"}',
    ];

    public const array CLI_HEADERS_RESPONSES = [
        'x-sdk-name: cli; x-sdk-platform: server; x-sdk-language: cli; '
            . 'x-sdk-version: 0.0.1; accept: application/json, text/plain',
    ];

    public const string HEADERS_RESPONSE = '__HEADERS_RESPONSE__';

    public const array CHANNEL_HELPER_RESPONSES = [
        'databases.db1.collections.col1.documents',
        'databases.db1.collections.col1.documents.doc1',
        'databases.db1.collections.col1.documents.doc1.create',
        'databases.db1.collections.col1.documents.doc1.upsert',
        'tablesdb.db1.tables.table1.rows',
        'tablesdb.db1.tables.table1.rows.row1',
        'tablesdb.db1.tables.table1.rows.row1.update',
        'account',
        'buckets.bucket1.files',
        'buckets.bucket1.files.file1',
        'buckets.bucket1.files.file1.delete',
        'functions.func2',
        'functions.func1',
        'executions.exec2',
        'executions.exec1',
        'documents',
        'rows',
        'files',
        'executions',
        'teams',
        'teams.team2',
        'teams.team1',
        'teams.team1.create',
        'memberships',
        'memberships.membership2',
        'memberships.membership1',
        'memberships.membership1.update',
        'presences',
        'presences.presence2',
        'presences.presence1',
        'presences.presence1.upsert',
        'presences.presence1.update',
        'presences.presence1.delete',
    ];

    public const array OPERATOR_HELPER_RESPONSES = [
        '{"method":"increment","values":[1]}',
        '{"method":"increment","values":[5,100]}',
        '{"method":"decrement","values":[1]}',
        '{"method":"decrement","values":[3,0]}',
        '{"method":"multiply","values":[2]}',
        '{"method":"multiply","values":[3,1000]}',
        '{"method":"divide","values":[2]}',
        '{"method":"divide","values":[4,1]}',
        '{"method":"modulo","values":[5]}',
        '{"method":"power","values":[2]}',
        '{"method":"power","values":[3,100]}',
        '{"method":"arrayAppend","values":["item1","item2"]}',
        '{"method":"arrayPrepend","values":["first","second"]}',
        '{"method":"arrayInsert","values":[0,"newItem"]}',
        '{"method":"arrayRemove","values":["oldItem"]}',
        '{"method":"arrayUnique","values":[]}',
        '{"method":"arrayIntersect","values":["a","b","c"]}',
        '{"method":"arrayDiff","values":["x","y"]}',
        '{"method":"arrayFilter","values":["equal","test"]}',
        '{"method":"stringConcat","values":["suffix"]}',
        '{"method":"stringReplace","values":["old","new"]}',
        '{"method":"toggle","values":[]}',
        '{"method":"dateAddDays","values":[7]}',
        '{"method":"dateSubDays","values":[3]}',
        '{"method":"dateSetNow","values":[]}',
        self::HEADERS_RESPONSE,
    ];
}
