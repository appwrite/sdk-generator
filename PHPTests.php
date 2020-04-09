<?php

namespace Tests;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PHPTests extends Base
{
    protected $langauge = 'php';

    protected $class = 'Appwrite\SDK\Language\PHP';

    protected $build = [];

    protected $execute = [
        'php-7.0' => 'docker run --rm -v $(pwd):/app -w /app php:7.0-cli php tests/languages/php/test.php',
        'php-7.1' => 'docker run --rm -v $(pwd):/app -w /app php:7.1-cli php tests/languages/php/test.php',
        'php-7.2' => 'docker run --rm -v $(pwd):/app -w /app php:7.2-cli php tests/languages/php/test.php',
        'php-7.3' => 'docker run --rm -v $(pwd):/app -w /app php:7.3-cli php tests/languages/php/test.php',
        'php-7.4' => 'docker run --rm -v $(pwd):/app -w /app php:7.4-cli php tests/languages/php/test.php',
    ];
}
