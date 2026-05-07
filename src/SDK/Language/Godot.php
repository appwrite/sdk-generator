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
                'template' => 'godot/addons/plugin.cfg.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/plugin.gd',
                'template' => 'godot/addons/plugin.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/{{ spec.title | caseSnake }}.gd',
                'template' => 'godot/addons/appwrite.gd.twig',
            ],
            [
                'scope' => 'copy',
                'destination' => 'addons/{{ spec.title | caseSnake }}/icon.svg',
                'template' => 'godot/addons/icon.svg',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/client.gd',
                'template' => 'godot/addons/client.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/service.gd',
                'template' => 'godot/addons/service.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/exception.gd',
                'template' => 'godot/addons/exception.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/id.gd',
                'template' => 'godot/addons/id.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/permission.gd',
                'template' => 'godot/addons/permission.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/role.gd',
                'template' => 'godot/addons/role.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/query.gd',
                'template' => 'godot/addons/query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{ spec.title | caseSnake }}/input_file.gd',
                'template' => 'godot/addons/input_file.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'addons/{{spec.title | caseSnake}}/operator.gd',
                'template' => 'godot/addons/operator.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_query.gd',
                'template' => 'godot/addons/tests/test_query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_roles.gd',
                'template' => 'godot/addons/tests/test_roles.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_query.gd',
                'template' => 'godot/addons/tests/test_query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_id.gd',
                'template' => 'godot/addons/tests/test_id.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_permission.gd',
                'template' => 'godot/addons/tests/test_permission.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_input_files.gd',
                'template' => 'godot/addons/tests/test_input_files.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'tests/test_operator.gd',
                'template' => 'godot/addons/tests/test_operator.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => '.env',
                'template' => 'godot/.env.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => 'addons/{{ spec.title | caseSnake }}/enums/{{ enum.name | caseSnake }}.gd',
                'template' => 'godot/addons/enums/enum.gd.twig',
            ],
            [
                'scope' => 'service',
                'destination' => 'addons/{{ spec.title | caseSnake }}/services/{{ service.name | caseSnake }}.gd',
                'template' => 'godot/addons/services/service.gd.twig',
            ],
            [
                'scope' => 'definition',
                'destination' => 'addons/{{ spec.title | caseSnake }}/models/{{ definition.name | caseSnake }}.gd',
                'template' => 'godot/addons/models/model.gd.twig',
            ],
            [
                'scope' => 'method',
                'destination' => 'docs/examples/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template' => 'godot/docs/example.md.twig',
            ],
            [
                'scope' => 'copy',
                'destination' => '.editorconfig',
                'template' => 'godot/.editorconfig',
            ],
            [
                'scope' => 'copy',
                'destination' => 'menu.gd',
                'template' => 'godot/menu.gd',
            ],
            [
                'scope' => 'copy',
                'destination' => 'Menu.tscn',
                'template' => 'godot/Menu.tscn',
            ],
            [
                'scope' => 'default',
                'destination' => 'project.godot',
                'template' => 'godot/project.godot.twig',
            ],
            [
                'scope' => 'copy',
                'destination' => 'icon.svg',
                'template' => 'godot/addons/icon.svg',
            ]
        ];
    }
}