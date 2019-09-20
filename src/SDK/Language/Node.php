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
                'destination'   => 'index.js',
                'template'      => '/node/index.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/client.js',
                'template'      => '/node/lib/client.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/service.js',
                'template'      => '/node/lib/service.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.js',
                'template'      => '/node/lib/services/service.js.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => '/node/README.md.twig',
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