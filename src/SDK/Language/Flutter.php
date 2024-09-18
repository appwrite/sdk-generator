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
                'destination'   => '/lib/enums.dart',
                'template'      => 'dart/lib/enums.dart.twig',
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
                'destination'   => 'lib/payload.dart',
                'template'      => 'dart/lib/payload.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'lib/src/payload_io.dart',
                'template'      => 'dart/lib/src/payload_io.dart.twig',
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
                'template'      => 'dart/lib/src/client_mixin.dart.twig',
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
                'scope'         => 'service',
                'destination'   => '/test/services/{{service.name | caseDash}}_test.dart',
                'template'      => 'dart/test/services/service_test.dart.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/test/src/models/{{definition.name | caseSnake }}_test.dart',
                'template'      => 'dart/test/src/models/model_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/id_test.dart',
                'template'      => 'dart/test/id_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/permission_test.dart',
                'template'      => 'dart/test/permission_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/query_test.dart',
                'template'      => 'dart/test/query_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/role_test.dart',
                'template'      => 'dart/test/role_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/cookie_manager_test.dart',
                'template'      => 'flutter/test/src/cookie_manager_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/interceptor_test.dart',
                'template'      => 'flutter/test/src/interceptor_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/realtime_response_test.dart',
                'template'      => 'flutter/test/src/realtime_response_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/realtime_response_connected_test.dart',
                'template'      => 'flutter/test/src/realtime_response_connected_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/realtime_subscription_test.dart',
                'template'      => 'flutter/test/src/realtime_subscription_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/enums_test.dart',
                'template'      => 'dart/test/src/enums_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/upload_progress_test.dart',
                'template'      => 'dart/test/src/upload_progress_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/payload_test.dart',
                'template'      => 'dart/test/src/payload_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/exception_test.dart',
                'template'      => 'dart/test/src/exception_test.dart.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/test/src/response_test.dart',
                'template'      => 'dart/test/src/response_test.dart.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'flutter/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'flutter/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/format.yml',
                'template'      => 'flutter/.github/workflows/format.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'lib/src/enums/{{ enum.name | caseSnake }}.dart',
                'template'      => 'dart/lib/src/enums/enum.dart.twig',
            ],
        ];
    }
}
