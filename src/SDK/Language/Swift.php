<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Swift extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Swift';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return [
            "class",
            "deinit",
            "enum",
            "extension",
            "func",
            "import",
            "init",
            "internal",
            "let",
            "operator",
            "private",
            "protocol",
            "public",
            "static",
            "struct",
            "subscript",
            "typealias",
            "var",
            "break",
            "case",
            "continue",
            "default",
            "do",
            "else",
            "fallthrough",
            "for",
            "if",
            "in",
            "return",
            "switch",
            "where",
            "while",
            "as",
            "dynamicType",
            "false",
            "is",
            "nil",
            "self",
            "super",
            "true",
            "associativity",
            "convenience",
            "dynamic",
            "didSet",
            "final",
            "get",
            "infix",
            "inout",
            "lazy",
            "left",
            "mutating",
            "none",
            "nonmutating",
            "optional",
            "override",
            "postfix",
            "precedence",
            "prefix",
            "required",
            "right",
            "set",
            "unowned",
            "weak",
            "willSet",
            "Type"
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [
            'enum' => 'xenum'
        ];
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'swift/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'swift/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'swift/LICENSE.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Package.swift',
                'template'      => 'swift/Package.swift.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'swift/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Tests/{{ spec.title | caseUcfirst}}Tests/Tests.swift',
                'template'      => 'swift/Tests/Tests.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Client.swift',
                'template'      => 'swift/Sources/Client.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/{{ spec.title | caseUcfirst}}Error.swift',
                'template'      => '/swift/Sources/Models/Error.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/Payload.swift',
                'template'      => 'swift/Sources/Models/Payload.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Permission.swift',
                'template'      => 'swift/Sources/Permission.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Role.swift',
                'template'      => 'swift/Sources/Role.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/ID.swift',
                'template'      => 'swift/Sources/ID.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Query.swift',
                'template'      => 'swift/Sources/Query.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/UploadProgress.swift',
                'template'      => 'swift/Sources/Models/UploadProgress.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/Codable+JSON.swift',
                'template'      => 'swift/Sources/Extensions/Codable+JSON.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/Cookie+Codable.swift',
                'template'      => 'swift/Sources/Extensions/Cookie+Codable.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Extensions/HTTPClientRequest+Cookies.swift',
                'template'      => 'swift/Sources/Extensions/HTTPClientRequest+Cookies.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/StreamingDelegate.swift',
                'template'      => 'swift/Sources/StreamingDelegate.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/Service.swift',
                'template'      => 'swift/Sources/Service.swift.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/iOS/IOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/IOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/iOS/UIDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/UIDevice+ModelName.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/Linux/LinuxDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Linux/LinuxDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/macOS/MacOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/macOS/MacOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/watchOS/WatchOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/watchOS/WatchOSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/watchOS/WKInterfaceDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/watchOS/WKInterfaceDevice+ModelName.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/macOS/CwlSysCtl.swift',
                'template'      => 'swift/Sources/DeviceInfo/macOS/CwlSysCtl.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/Windows/WindowsDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Windows/WindowsDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/OSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/OSDeviceInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Apple/PackageInfo+Apple.swift',
                'template'      => 'swift/Sources/PackageInfo/Apple/PackageInfo+Apple.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Linux/PackageInfo+Linux.swift',
                'template'      => 'swift/Sources/PackageInfo/Linux/PackageInfo+Linux.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Windows/PackageInfo+Windows.swift',
                'template'      => 'swift/Sources/PackageInfo/Windows/PackageInfo+Windows.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/OSPackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/OSPackageInfo.swift',
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/PackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/PackageInfo.swift',
            ],
            [
                'scope'         => 'service',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.swift',
                'template'      => 'swift/Sources/Services/Service.swift.twig',
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/{{ definition.name | caseUcfirst }}.swift',
                'template'      => '/swift/Sources/Models/Model.swift.twig',
            ],
            [
                'scope' => 'enum',
                'destination' => '/Sources/{{ spec.title | caseUcfirst}}Enums/{{ enum.name | caseUcfirst }}.swift',
                'template' => '/swift/Sources/Enums/Enum.swift.twig',
            ]
        ];
    }

    /**
     * @param array $parameter
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (isset($parameter['enumName'])) {
            return ($spec['title'] ?? '') . 'Enums.' . \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return ($spec['title'] ?? '') . 'Enums.' . \ucfirst($parameter['name']);
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'Int',
            self::TYPE_NUMBER => 'Double',
            self::TYPE_STRING => 'String',
            self::TYPE_FILE,
            self::TYPE_PAYLOAD => 'Payload',
            self::TYPE_BOOLEAN => 'Bool',
            self::TYPE_ARRAY => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? '[' . $this->getTypeName($parameter['array']) . ']'
                : '[Any]',
            self::TYPE_OBJECT => 'Any',
            default => $parameter['type'],
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
                case self::TYPE_NUMBER:
                    $output = "0";
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= 'nil';
                    break;
                default:
                    echo $type;
            }
        } else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_NUMBER:
                    $output .= sprintf("%.1f", $default);
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$default}\"";
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'nil';
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
                    $output .= 'Payload.fromFile("/path/to/file.png")';
                    break;
                case self::TYPE_PAYLOAD:
                    $output .= 'Payload.fromString("<BODY>")'; // TODO: Update to fromJson()
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= "0";
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '[:]';
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
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "\"{$example}\"";
                    break;
                case self::TYPE_OBJECT:
                    $output .= '[:]';
                    break;
            }
        }

        return $output;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('swiftComment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "    /// " . wordwrap($line, 75, "\n    /// ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('returnType', function (array $method, array $spec, string $generic = 'T') {
                return $this->getReturnType($method, $spec, $generic);
            }),
            new TwigFilter('modelType', function (array $property, array $spec, string $generic = 'T : Codable') {
                return $this->getModelType($property, $spec, $generic);
            }),
            new TwigFilter('propertyType', function (array $property, array $spec, string $generic = 'T') {
                return $this->getPropertyType($property, $spec, $generic);
            }),
            new TwigFilter('hasGenericType', function (string $model, array $spec) {
                return $this->hasGenericType($model, $spec);
            }),
            new TwigFilter('escapeSwiftKeyword', function ($value) {
                if (\in_array($value, $this->getKeywords())) {
                    return "`{$value}`";
                }
                return $value;
            }),
            new TwigFilter('caseEnumKey', function (string $value) {
                if (isset($this->getIdentifierOverrides()[$value])) {
                    $value = $this->getIdentifierOverrides()[$value];
                }
                return $this->toCamelCase($value);
            }),
        ];
    }

    protected function getReturnType(array $method, array $spec, string $generic): string
    {
        if ($method['type'] === 'webAuth') {
            return 'String?';
        }
        if ($method['type'] === 'location') {
            return 'ByteBuffer';
        }

        if (
            !\array_key_exists('responseModel', $method)
            || empty($method['responseModel'])
            || $method['responseModel'] === 'any'
        ) {
            return 'Any';
        }

        $ret = $this->toPascalCase($method['responseModel']);

        if ($this->hasGenericType($method['responseModel'], $spec)) {
            $ret .= '<' . $generic . '>';
        }

        return \ucfirst($spec['title']) . 'Models.' . $ret;
    }

    protected function getModelType(array $definition, array $spec, string $generic): string
    {
        if ($this->hasGenericType($definition['name'], $spec)) {
            return $this->toPascalCase($definition['name']) . '<' . $generic . '>';
        }
        return $this->toPascalCase($definition['name']);
    }

    protected function getPropertyType(array $property, array $spec, string $generic): string
    {
        if (\array_key_exists('sub_schema', $property)) {
            $type = $this->toPascalCase($property['sub_schema']);

            if ($this->hasGenericType($property['sub_schema'], $spec)) {
                $type .= '<' . $generic . '>';
            }

            if ($property['type'] === 'array') {
                $type = '[' . $type . ']';
            }
        } else {
            $type = $this->getTypeName($property);
        }

        return $type;
    }

    protected function hasGenericType(?string $model, array $spec): string
    {
        if (empty($model) || $model === 'any') {
            return false;
        }

        $model = $spec['definitions'][$model];

        if ($model['additionalProperties']) {
            return true;
        }

        foreach ($model['properties'] as $property) {
            if (!\array_key_exists('sub_schema', $property) || !$property['sub_schema']) {
                continue;
            }

            return $this->hasGenericType($property['sub_schema'], $spec);
        }

        return false;
    }
}
