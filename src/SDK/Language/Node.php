<?php

namespace Appwrite\SDK\Language;


class Node extends JS
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'NodeJS';
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'src/client.js',
                'template'      => '/node/src/client.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/sdk.min.js',
                'template'      => '/node/src/sdk.js.twig',
                'minify'        => true,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/node/REDAME.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => '/node/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => '/node/package.json.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => '/node/docs/example.md.twig',
                'minify'        => false,
            ],
        ];
    }
}