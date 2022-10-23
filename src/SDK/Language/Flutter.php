<?php

namespace Appwrite\SDK\Language;

class Flutter extends Dart
{
    /**
     * @var array
     */
    protected $params = [
        'packageName' => 'packageName',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Flutter';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/response.dart',
                'template'      => 'dart/lib/src/response.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/service.dart',
                'template'      => 'dart/lib/src/service.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/models/model.dart',
                'template'      => 'dart/lib/src/models/model_base.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/enums.dart',
                'template'      => 'dart/lib/src/enums.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/exception.dart',
                'template'      => 'dart/lib/src/exception.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/upload_progress.dart',
                'template'      => 'dart/lib/src/upload_progress.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/models.dart',
                'template'      => 'dart/lib/models.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/permission.dart',
                'template'      => 'dart/lib/permission.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/role.dart',
                'template'      => 'dart/lib/role.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/id.dart',
                'template'      => 'dart/lib/id.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/query.dart',
                'template'      => 'dart/lib/query.dart.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/lib/src/models/{{definition.name | caseSnake }}.dart',
                'template'      => 'dart/lib/src/models/model.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/src/input_file.dart',
                'template'      => 'dart/lib/src/input_file.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'flutter/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/README.md',
                'template'      => 'flutter/example/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/example/pubspec.yaml',
                'template'      => 'flutter/example/pubspec.yaml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'flutter/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'flutter/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client.dart',
                'template'      => 'flutter/lib/src/client.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_base.dart',
                'template'      => 'flutter/lib/src/client_base.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_browser.dart',
                'template'      => 'flutter/lib/src/client_browser.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_io.dart',
                'template'      => 'flutter/lib/src/client_io.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_mixin.dart',
                'template'      => 'flutter/lib/src/client_mixin.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/client_stub.dart',
                'template'      => 'flutter/lib/src/client_stub.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime.dart',
                'template'      => 'flutter/lib/src/realtime.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_base.dart',
                'template'      => 'flutter/lib/src/realtime_base.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_browser.dart',
                'template'      => 'flutter/lib/src/realtime_browser.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_io.dart',
                'template'      => 'flutter/lib/src/realtime_io.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_mixin.dart',
                'template'      => 'flutter/lib/src/realtime_mixin.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_stub.dart',
                'template'      => 'flutter/lib/src/realtime_stub.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_subscription.dart',
                'template'      => 'flutter/lib/src/realtime_subscription.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_message.dart',
                'template'      => 'flutter/lib/src/realtime_message.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_response.dart',
                'template'      => 'flutter/lib/src/realtime_response.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/realtime_response_connected.dart',
                'template'      => 'flutter/lib/src/realtime_response_connected.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/cookie_manager.dart',
                'template'      => 'flutter/lib/src/cookie_manager.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/src/interceptor.dart',
                'template'      => 'flutter/lib/src/interceptor.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/{{ language.params.packageName }}.dart',
                'template'      => 'flutter/lib/package.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_io.dart',
                'template'      => 'flutter/lib/client_io.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/client_browser.dart',
                'template'      => 'flutter/lib/client_browser.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/realtime_io.dart',
                'template'      => 'flutter/lib/realtime_io.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/lib/realtime_browser.dart',
                'template'      => 'flutter/lib/realtime_browser.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/pubspec.yaml',
                'template'      => 'flutter/pubspec.yaml.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
                'template'      => 'flutter/lib/services/service.dart.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'flutter/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.travis.yml',
                'template'      => 'flutter/.travis.yml.twig',
            ],
        ];
    }
}
