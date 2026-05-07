<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language\GDScript;

class Godot extends GDScript
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Godot';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope' => 'default',
                'destination' => 'CHANGELOG.md',
                'template' => 'godot/CHANGELOG.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'LICENSE',
                'template' => 'godot/LICENSE.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'README.md',
                'template' => 'godot/README.md.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/plugin.cfg',
                'template' => 'godot/src/plugin.cfg.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/plugin.gd',
                'template' => 'godot/src/plugin.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/{{ spec.title | caseSnake }}.gd',
                'template' => 'godot/src/appwrite.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/icon.svg',
                'template' => 'godot/src/icon.svg',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/client.gd',
                'template' => 'godot/src/client.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/service.gd',
                'template' => 'godot/src/service.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/exception.gd',
                'template' => 'godot/src/exception.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/id.gd',
                'template' => 'godot/src/id.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/permission.gd',
                'template' => 'godot/src/permission.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/role.gd',
                'template' => 'godot/src/role.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/query.gd',
                'template' => 'godot/src/query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/input_file.gd',
                'template' => 'godot/src/input_file.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/.env.example',
                'template' => 'godot/example.env.twig'
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{spec.title | caseSnake}}/operator.gd',
                'template' => 'godot/src/operator.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_query.gd',
                'template' => 'godot/src/tests/test_query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_roles.gd',
                'template' => 'godot/src/tests/test_roles.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_query.gd',
                'template' => 'godot/src/tests/test_query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_id.gd',
                'template' => 'godot/src/tests/test_id.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_permission.gd',
                'template' => 'godot/src/tests/test_permission.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_input_files.gd',
                'template' => 'godot/src/tests/test_input_files.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_operator.gd',
                'template' => 'godot/src/tests/test_operator.gd.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => 'addons/{{ spec.title | caseSnake }}/enums/{{ enum.name | caseSnake }}.gd',
                'template' => 'godot/src/enums/enum.gd.twig',
            ],
            [
                'scope' => 'service',
                'destination' => 'addons/{{ spec.title | caseSnake }}/services/{{ service.name | caseSnake }}.gd',
                'template' => 'godot/src/services/service.gd.twig',
            ],
            [
                'scope' => 'definition',
                'destination' => 'addons/{{ spec.title | caseSnake }}/models/{{ definition.name | caseSnake }}.gd',
                'template' => 'godot/src/models/model.gd.twig',
            ],
            [
                'scope' => 'method',
                'destination' => 'docs/examples/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template' => 'godot/docs/example.md.twig',
            ],
        ];
    }
}