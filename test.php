<?php

$contextOptions = [
    'ssl' => [
        'verify_peer' => false,
        'allow_self_signed' => true
    ]
];

$sslContext = stream_context_create($contextOptions);

var_dump(file_get_contents("http://localhost/", false, $sslContext));