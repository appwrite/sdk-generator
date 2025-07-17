<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Unity extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unity';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            'abstract',
            'add',
            'alias',
            'as',
            'ascending',
            'async',
            'await',
            'base',
            'bool',
            'break',
            'by',
            'byte',
            'case',
            'catch',
            'char',
            'checked',
            'class',
            'const',
            'continue',
            'decimal',
            'default',
            'delegate',
            'do',
            'double',
            'descending',
            'dynamic',
            'else',
            'enum',
            'equals',
            'event',
            'explicit',
            'extern',
            'false',
            'finally',
            'fixed',
            'float',
            'for',
            'foreach',
            'from',
            'get',
            'global',
            'goto',
            'group',
            'if',
            'implicit',
            'in',
            'int',
            'interface',
            'internal',
            'into',
            'is',
            'join',
            'let',
            'lock',
            'long',
            'nameof',
            'namespace',
            'new',
            'null',
            'object',
            'on',
            'operator',
            'orderby',
            'out',
            'override',
            'params',
            'partial',
            'private',
            'protected',
            'public',
            'readonly',
            'ref',
            'remove',
            'return',
            'sbyte',
            'sealed',
            'select',
            'set',
            'short',
            'sizeof',
            'stackalloc',
            'static',
            'string',
            'struct',
            'switch',
            'this',
            'throw',
            'true',
            'try',
            'typeof',
            'uint',
            'ulong',
            'unchecked',
            'unmanaged',
            'unsafe',
            'ushort',
            'using',
            'using static',
            'value',
            'var',
            'virtual',
            'void',
            'volatile',
            'when',
            'where',
            'while',
            'yield',
            'path',
            // Unity specific keywords
            'GameObject',
            'MonoBehaviour',
            'Transform',
            'Component',
            'ScriptableObject',
            'UnityEngine',
            'UnityEditor'
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [
            'Jwt' => 'JWT',
            'Domain' => 'XDomain',
        ];
    }

    public function getPropertyOverrides(): array
    {
        return [
            'provider' => [
                'Provider' => 'MessagingProvider',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return 'Appwrite.Enums.' . \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return 'Appwrite.Enums.' . \ucfirst($parameter['name']);
        }
        if (isset($parameter['items'])) {
            // Map definition nested type to parameter nested type
            $parameter['array'] = $parameter['items'];
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'long',
            self::TYPE_NUMBER => 'double',
            self::TYPE_STRING => 'string',
            self::TYPE_BOOLEAN => 'bool',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? 'List<' . $this->getTypeName($parameter['array']) . '>'
                : 'List<object>',
            self::TYPE_OBJECT => 'object',
            default => $parameter['type']
        };
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'null';
                    break;
            }
        }

        return $output;
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamExample(array $param): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if (empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_FILE:
                    $output .= 'InputFile.FromPath("./path-to-files/image.jpg")';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '[object]';
                    break;
                case self::TYPE_ARRAY:
                    if (\str_starts_with($example, '[')) {
                        $example = \substr($example, 1);
                    }
                    if (\str_ends_with($example, ']')) {
                        $example = \substr($example, 0, -1);
                    }
                    if (!empty($example)) {
                        $output .= 'new List<' . $this->getTypeName($param['array']) . '>() {' . $example . '}';
                    } else {
                        $output .= 'new List<' . $this->getTypeName($param['array']) . '>()';
                    }
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_FILE:
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $example;
                    break;
                case self::TYPE_OBJECT:
                    $output .= '[object]';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
            }
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $files = [
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'unity/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '/icon.png',
                'template'      => 'unity/icon.png',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'unity/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'unity/README.md.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'unity/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package.json',
                'template'      => 'unity/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}.asmdef',
                'template'      => 'unity/Assets/Runtime/Appwrite.asmdef.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Client.cs',
                'template'      => 'unity/Assets/Runtime/Client.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}Config.cs',
                'template'      => 'unity/Assets/Runtime/AppwriteConfig.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}Manager.cs',
                'template'      => 'unity/Assets/Runtime/AppwriteManager.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Realtime.cs',
                'template'      => 'unity/Assets/Runtime/Realtime.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef',
                'template'      => 'unity/Assets/Editor/Appwrite.Editor.asmdef.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}SetupAssistant.cs',
                'template'      => 'unity/Assets/Editor/AppwriteSetupAssistant.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Editor/{{ spec.title | caseUcfirst }}SetupWindow.cs',
                'template'      => 'unity/Assets/Editor/AppwriteSetupWindow.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/{{ spec.title | caseUcfirst }}Exception.cs',
                'template'      => 'unity/Assets/Runtime/Exception.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Utilities/AppwriteUtilities.cs',
                'template'      => 'unity/Assets/Runtime/Utilities/AppwriteUtilities.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/ID.cs',
                'template'      => 'unity/Assets/Runtime/ID.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Permission.cs',
                'template'      => 'unity/Assets/Runtime/Permission.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Query.cs',
                'template'      => 'unity/Assets/Runtime/Query.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Role.cs',
                'template'      => 'unity/Assets/Runtime/Role.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/CookieContainer.cs',
                'template'      => 'unity/Assets/Runtime/CookieContainer.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Converters/ValueClassConverter.cs',
                'template'      => 'unity/Assets/Runtime/Converters/ValueClassConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Converters/ObjectToInferredTypesConverter.cs',
                'template'      => 'unity/Assets/Runtime/Converters/ObjectToInferredTypesConverter.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Extensions/Extensions.cs',
                'template'      => 'unity/Assets/Runtime/Extensions/Extensions.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Models/OrderType.cs',
                'template'      => 'unity/Assets/Runtime/Models/OrderType.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Models/UploadProgress.cs',
                'template'      => 'unity/Assets/Runtime/Models/UploadProgress.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Models/InputFile.cs',
                'template'      => 'unity/Assets/Runtime/Models/InputFile.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Services/Service.cs',
                'template'      => 'unity/Assets/Runtime/Services/Service.cs.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'Assets/Runtime/Services/{{service.name | caseUcfirst}}.cs',
                'template'      => 'unity/Assets/Runtime/Services/ServiceTemplate.cs.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => 'Assets/Runtime/Models/{{ definition.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'unity/Assets/Runtime/Models/Model.cs.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'Assets/Runtime/Enums/{{ enum.name | caseUcfirst | overrideIdentifier }}.cs',
                'template'      => 'unity/Assets/Runtime/Enums/Enum.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Runtime/Enums/IEnum.cs',
                'template'      => 'unity/Assets/Runtime/Enums/IEnum.cs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Assets/Samples/AppwriteExample/AppwriteExample.cs',
                'template'      => 'unity/Assets/Samples/AppwriteExample/AppwriteExample.cs.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Plugins/Microsoft.Bcl.AsyncInterfaces.dll',
                'template'      => 'unity/Assets/Runtime/Plugins/Microsoft.Bcl.AsyncInterfaces.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Plugins/System.IO.Pipelines.dll',
                'template'      => 'unity/Assets/Runtime/Plugins/System.IO.Pipelines.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Plugins/System.Runtime.CompilerServices.Unsafe.dll',
                'template'      => 'unity/Assets/Runtime/Plugins/System.Runtime.CompilerServices.Unsafe.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Plugins/System.Text.Encodings.Web.dll',
                'template'      => 'unity/Assets/Runtime/Plugins/System.Text.Encodings.Web.dll',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Assets/Plugins/System.Text.Json.dll',
                'template'      => 'unity/Assets/Runtime/Plugins/System.Text.Json.dll',
            ],
            // Packages
            [
                'scope'         => 'copy',
                'destination'   => 'Packages/manifest.json',
                'template'      => 'unity/Packages/manifest.json',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'Packages/packages-lock.json',
                'template'      => 'unity/Packages/packages-lock.json',
            ],
            // ProjectSettings
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/AudioManager.asset',
                'template'      => 'unity/ProjectSettings/AudioManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/boot.config',
                'template'      => 'unity/ProjectSettings/boot.config',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/ClusterInputManager.asset',
                'template'      => 'unity/ProjectSettings/ClusterInputManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/DynamicsManager.asset',
                'template'      => 'unity/ProjectSettings/DynamicsManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/EditorBuildSettings.asset',
                'template'      => 'unity/ProjectSettings/EditorBuildSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/EditorSettings.asset',
                'template'      => 'unity/ProjectSettings/EditorSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/GraphicsSettings.asset',
                'template'      => 'unity/ProjectSettings/GraphicsSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/InputManager.asset',
                'template'      => 'unity/ProjectSettings/InputManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/MemorySettings.asset',
                'template'      => 'unity/ProjectSettings/MemorySettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/NavMeshAreas.asset',
                'template'      => 'unity/ProjectSettings/NavMeshAreas.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/NetworkManager.asset',
                'template'      => 'unity/ProjectSettings/NetworkManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/PackageManagerSettings.asset',
                'template'      => 'unity/ProjectSettings/PackageManagerSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/Physics2DSettings.asset',
                'template'      => 'unity/ProjectSettings/Physics2DSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/PresetManager.asset',
                'template'      => 'unity/ProjectSettings/PresetManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/ProjectSettings.asset',
                'template'      => 'unity/ProjectSettings/ProjectSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/ProjectVersion.txt',
                'template'      => 'unity/ProjectSettings/ProjectVersion.txt',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/QualitySettings.asset',
                'template'      => 'unity/ProjectSettings/QualitySettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/TagManager.asset',
                'template'      => 'unity/ProjectSettings/TagManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/TimeManager.asset',
                'template'      => 'unity/ProjectSettings/TimeManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/UnityConnectSettings.asset',
                'template'      => 'unity/ProjectSettings/UnityConnectSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/VersionControlSettings.asset',
                'template'      => 'unity/ProjectSettings/VersionControlSettings.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/VFXManager.asset',
                'template'      => 'unity/ProjectSettings/VFXManager.asset',
            ],
            [
                'scope'         => 'copy',
                'destination'   => 'ProjectSettings/XRSettings.asset',
                'template'      => 'unity/ProjectSettings/XRSettings.asset',
            ],
        ];

        // Filter out problematic files in test mode
        // Check if we're in test mode by looking for a global variable
        if (isset($GLOBALS['UNITY_TEST_MODE']) && $GLOBALS['UNITY_TEST_MODE'] === true) {
            $excludeInTest = [
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Client.cs',
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Config.cs',
                'Assets/Runtime/{{ spec.title | caseUcfirst }}Manager.cs',
                'Assets/Editor/{{ spec.title | caseUcfirst }}.Editor.asmdef',
                'Assets/Editor/{{ spec.title | caseUcfirst }}SetupAssistant.cs',
                'Assets/Editor/{{ spec.title | caseUcfirst }}SetupWindow.cs',
            ];

            $files = array_filter($files, function ($file) use ($excludeInTest): bool {
                return !in_array($file['destination'], $excludeInTest);
            });
        }

        return $files;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('unityComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "        /// " . wordwrap($line, 75, "\n        /// ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toPascalCase($value);
            }),
            new TwigFilter('overrideProperty', function (string $property, string $class) {
                if (isset($this->getPropertyOverrides()[$class][$property])) {
                    return $this->getPropertyOverrides()[$class][$property];
                }
                return $property;
            }),
        ];
    }
}
