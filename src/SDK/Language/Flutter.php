<?php

namespace Appwrite\SDK\Language;

class Flutter extends Dart {

    /**
     * @var array
     */
    protected $params = [
        'packageName' => 'packageName',
    ];

    /**
     * @return string
     */
    public function getName()
    {
        return 'Flutter';
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'flutter/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/README.md',
                'template'      => 'flutter/example/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/pubspec.yaml',
                'template'      => 'flutter/example/pubspec.yaml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'flutter/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'flutter/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client.dart',
                'template'      => 'flutter/lib/client.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/{{ language.params.packageName }}.dart',
                'template'      => 'flutter/lib/package.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pubspec.yaml',
                'template'      => 'flutter/pubspec.yaml.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/service.dart',
                'template'      => 'flutter/lib/service.dart.twig',
                'minify'        => false,
            ],
            [
				'scope'         => 'default',
				'destination'   => '/lib/enums.dart',
				'template'      => 'flutter/lib/enums.dart.twig',
				'minify'        => false,
			],
            [
				'scope'         => 'default',
				'destination'   => '/lib/exception.dart',
				'template'      => 'flutter/lib/exception.dart.twig',
				'minify'        => false,
			],
            [
				'scope'         => 'default',
				'destination'   => '/lib/query.dart',
				'template'      => 'flutter/lib/query.dart.twig',
				'minify'        => false,
			],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => 'flutter/lib/services/service.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'flutter/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'flutter/.travis.yml.twig',
                'minify'        => false,
            ],
        ];
    }
}
