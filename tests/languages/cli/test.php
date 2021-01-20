<?php

$command = "pwd";
$output = [];
exec($command, $output);
var_dump($output);


// Foo Service
// $command = "echo \"yes\nyes\nyes\nyes\n\" | docker run cli foo get  --x=\"['string in array']\"  --y=\"123\"  --z=\"string\"";
$command = "echo \"yes\nyes\nyes\nyes\n";
$output = [];
exec($command, $output);
var_dump($output);