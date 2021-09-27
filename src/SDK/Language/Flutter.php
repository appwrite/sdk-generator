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
                'destination'   => '/lib/src/client.dart',
                'template'      => 'flutter/lib/src/client.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_base.dart',
                'template'      => 'flutter/lib/src/client_base.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_browser.dart',
                'template'      => 'flutter/lib/src/client_browser.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_io.dart',
                'template'      => 'flutter/lib/src/client_io.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_mixin.dart',
                'template'      => 'flutter/lib/src/client_mixin.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_stub.dart',
                'template'      => 'flutter/lib/src/client_stub.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime.dart',
                'template'      => 'flutter/lib/src/realtime.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_base.dart',
                'template'      => 'flutter/lib/src/realtime_base.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_browser.dart',
                'template'      => 'flutter/lib/src/realtime_browser.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_io.dart',
                'template'      => 'flutter/lib/src/realtime_io.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_mixin.dart',
                'template'      => 'flutter/lib/src/realtime_mixin.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_stub.dart',
                'template'      => 'flutter/lib/src/realtime_stub.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_subscription.dart',
                'template'      => 'flutter/lib/src/realtime_subscription.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_message.dart',
                'template'      => 'flutter/lib/src/realtime_message.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_response.dart',
                'template'      => 'flutter/lib/src/realtime_response.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_response_connected.dart',
                'template'      => 'flutter/lib/src/realtime_response_connected.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/cookie_manager.dart',
                'template'      => 'flutter/lib/src/cookie_manager.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/interceptor.dart',
                'template'      => 'flutter/lib/src/interceptor.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/redirect_browser.dart',
                'template'      => 'flutter/lib/src/redirect_browser.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/redirect_stub.dart',
                'template'      => 'flutter/lib/src/redirect_stub.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/response.dart',
                'template'      => 'flutter/lib/src/response.dart.twig',
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
                'destination'   => '/lib/client_io.dart',
                'template'      => 'flutter/lib/client_io.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_browser.dart',
                'template'      => 'flutter/lib/client_browser.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/realtime_io.dart',
                'template'      => 'flutter/lib/realtime_io.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/realtime_browser.dart',
                'template'      => 'flutter/lib/realtime_browser.dart.twig',
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
                'destination'   => '/lib/src/service.dart',
                'template'      => 'flutter/lib/src/service.dart.twig',
                'minify'        => false,
            ],
            [
				'scope'         => 'default',
				'destination'   => '/lib/src/enums.dart',
				'template'      => 'flutter/lib/src/enums.dart.twig',
				'minify'        => false,
			],
            [
				'scope'         => 'default',
				'destination'   => '/lib/src/exception.dart',
				'template'      => 'flutter/lib/src/exception.dart.twig',
				'minify'        => false,
			],
            [
				'scope'         => 'default',
				'destination'   => '/lib/models.dart',
				'template'      => 'flutter/lib/models.dart.twig',
				'minify'        => false,
			],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => 'flutter/lib/services/service.dart.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/src/models/{{definition.name | caseSnake }}.dart',
                'template'      => '/flutter/lib/src/models/model.dart.twig',
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
