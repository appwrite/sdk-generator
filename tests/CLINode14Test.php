<?php

namespace Tests;

use Appwrite\SDK\Language;
use Appwrite\SDK\Language\CLI;

class CLINode14Test extends Base
{
    protected string $sdkName = 'cli';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'cli';
    protected string $version = '0.0.1';

    protected string $language = 'cli';
    protected string $class = 'Appwrite\SDK\Language\CLI';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/cli node:16-alpine npm install',
        'cp tests/languages/cli/test.js tests/sdks/cli/test.js'
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/cli node:14-alpine node test.js';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        'POST:/v1/mock/tests/general/upload:passed', //large file
    ];

    public function getLanguage(): Language
    {
        $language  = new CLI();
        $language->setNPMPackage('appwrite-cli');
        $language->setExecutableName('appwrite');
        $language->setLogo(json_encode("
       _                            _ _           ___   __   _____ 
      /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
     //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
    /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
    \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
        |_|   |_|                                                

    "));
        $language->setLogoUnescaped("
       _                            _ _           ___   __   _____ 
      /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
     //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
    /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
    \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
            |_|   |_|                                                ");

        return $language;
    }
}
