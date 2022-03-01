<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Swift extends Language {

    /**
     * @return string
     */
    public function getName()
    {
        return 'Swift';
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
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
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides()
    {
        return [];
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
                'template'      => 'swift/README.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'swift/CHANGELOG.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'swift/LICENSE.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => 'Package.swift',
                'template'      => 'swift/Package.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
                'template'      => 'swift/docs/example.md.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Tests/{{ spec.title | caseUcfirst}}Tests/Tests.swift',
                'template'      => 'swift/Tests/Tests.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Client.swift',
                'template'      => 'swift/Sources/Client.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Models/{{ spec.title | caseUcfirst}}Error.swift',
                'template'      => '/swift/Sources/Models/Error.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Models/File.swift',
                'template'      => 'swift/Sources/Models/File.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Models/Query.swift',
                'template'      => 'swift/Sources/Models/Query.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Models/UploadProgress.swift',
                'template'      => 'swift/Sources/Models/UploadProgress.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Extensions/Codable+JSON.swift',
                'template'      => 'swift/Sources/Extensions/Codable+JSON.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Extensions/Cookie+Codable.swift',
                'template'      => 'swift/Sources/Extensions/Cookie+Codable.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Extensions/HTTPClientRequest+Cookies.swift',
                'template'      => 'swift/Sources/Extensions/HTTPClientRequest+Cookies.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/StreamingDelegate.swift',
                'template'      => 'swift/Sources/StreamingDelegate.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/Service.swift',
                'template'      => 'swift/Sources/Service.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/iOS/iOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/iOSDeviceInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/iOS/UIDevice+ModelName.swift',
                'template'      => 'swift/Sources/DeviceInfo/iOS/UIDevice+ModelName.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/Linux/LinuxDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Linux/LinuxDeviceInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/MacOS/MacOSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/MacOS/MacOSDeviceInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/MacOS/CwlSysCtl.swift',
                'template'      => 'swift/Sources/DeviceInfo/MacOS/CwlSysCtl.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/Windows/WindowsDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/Windows/WindowsDeviceInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/DeviceInfo/OSDeviceInfo.swift',
                'template'      => 'swift/Sources/DeviceInfo/OSDeviceInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Apple/PackageInfo+Apple.swift',
                'template'      => 'swift/Sources/PackageInfo/Apple/PackageInfo+Apple.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Linux/PackageInfo+Linux.swift',
                'template'      => 'swift/Sources/PackageInfo/Linux/PackageInfo+Linux.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/Windows/PackageInfo+Windows.swift',
                'template'      => 'swift/Sources/PackageInfo/Windows/PackageInfo+Windows.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/OSPackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/OSPackageInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/PackageInfo/PackageInfo.swift',
                'template'      => 'swift/Sources/PackageInfo/PackageInfo.swift',
                'minify'        => false,
            ],
            [
                'scope'         => 'service',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Services/{{service.name | caseUcfirst}}.swift',
                'template'      => 'swift/Sources/Services/Service.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'definition',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}Models/{{ definition.name | caseUcfirst }}.swift',
                'template'      => '/swift/Sources/Models/Model.swift.twig',
                'minify'        => false,
            ],
        ];
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeName($type)
    {
        switch ($type) {
            case self::TYPE_INTEGER:
                return 'Int';
            case self::TYPE_NUMBER:
                return 'Double';
            case self::TYPE_STRING:
                return 'String';
            case self::TYPE_FILE:
                return 'File';
            case self::TYPE_BOOLEAN:
                return 'Bool';
            case self::TYPE_ARRAY:
                 return '[Any]';
            case self::TYPE_OBJECT:
                return 'Any';
        }

        return $type;
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param)
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if($required) {
            return '';
        }

        $output = ' = ';

        if(empty($default) && $default !== 0 && $default !== false) {
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
        }
        else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_NUMBER:
                    $output .= sprintf("%.1f",$default);
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
    public function getParamExample(array $param)
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_FILE:
                    $output .= 'File(name: "image.jpg", buffer: yourByteBuffer)';
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
            }
        }
        else {
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
            }
        }

        return $output;
    }
}
