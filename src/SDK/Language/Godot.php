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

    public function getVersion(): string
    {
        return '4.0';
    }

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
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/client.gd',
                'template' => 'godot/src/client.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/service.gd',
                'template' => 'godot/src/service.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/exception.gd',
                'template' => 'godot/src/exception.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/id.gd',
                'template' => 'godot/src/id.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/permission.gd',
                'template' => 'godot/src/permission.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/role.gd',
                'template' => 'godot/src/role.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/query.gd',
                'template' => 'godot/src/query.gd.twig',
            ],
            [
                'scope' => 'default',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/input_file.gd',
                'template' => 'godot/src/input_file.gd.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/enums/{{ enum.name | caseSnake }}.gd',
                'template' => 'godot/src/enums/enum.gd.twig',
            ],
            [
                'scope' => 'service',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/services/{{ service.name | caseSnake }}.gd',
                'template' => 'godot/src/services/service.gd.twig',
            ],
            [
                'scope' => 'definition',
                'destination' => 'src/addons/{{ sdk.name | caseLower }}/models/{{ definition.name | caseSnake }}.gd',
                'template' => 'godot/src/models/model.gd.twig',
            ],
            [
                'scope' => 'method',
                'destination' => 'src/addons/{{sdk.name | caseLower}}/docs/{{service.name | caseSnake}}/{{method.name | caseSnake}}.md',
                'template' => 'godot/docs/example.md.twig',
            ],
        ];
    }

    public function getDocsUrl(): string
    {
        return 'https://appwrite.io/docs/sdks/godot';
    }
}