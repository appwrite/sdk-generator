<?php

namespace Appwrite\SDK\Language;

use Twig\TwigFilter;

class Java extends Kotlin
{
    public function getName(): string
    {
        return 'Java';
    }

    public function getArrayOf(string $elements): string
    {
        return 'List.of(' . $elements . ')';
    }

    /**
     * @param array $parameter
     * @param array $spec
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        if (
            ($parameter['type'] ?? null) === self::TYPE_ARRAY
            && (isset($parameter['enumName']) || !empty($parameter['enumValues']))
        ) {
            $enumType = isset($parameter['enumName'])
                ? \ucfirst($parameter['enumName'])
                : \ucfirst($parameter['name']);

            return 'List<io.appwrite.enums.' . $enumType . '>';
        }

        if (isset($parameter['enumName'])) {
            return 'io.appwrite.enums.' . \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return 'io.appwrite.enums.' . \ucfirst($parameter['name']);
        }
        if (!empty($parameter['array']['model'])) {
            return 'List<io.appwrite.models.' . $this->toPascalCase($parameter['array']['model']) . '>';
        }
        if (!empty($parameter['model'])) {
            $modelType = 'io.appwrite.models.' . $this->toPascalCase($parameter['model']);
            return $parameter['type'] === self::TYPE_ARRAY ? 'List<' . $modelType . '>' : $modelType;
        }
        if (isset($parameter['items'])) {
            $parameter['array'] = $parameter['items'];
        }
        return match ($parameter['type']) {
            self::TYPE_INTEGER => 'Long',
            self::TYPE_NUMBER  => 'Double',
            self::TYPE_STRING  => 'String',
            self::TYPE_FILE    => 'InputFile',
            self::TYPE_BOOLEAN => 'Boolean',
            self::TYPE_ARRAY   => (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type']))
                ? 'List<' . $this->getTypeName($parameter['array']) . '>'
                : 'List<Object>',
            self::TYPE_OBJECT  => 'Object',
            default            => $parameter['type'],
        };
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type     = $param['type'] ?? '';
        $default  = $param['default'] ?? '';
        $required = $param['required'] ?? '';

        if ($required) {
            return '';
        }

        $output = ' = ';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= 'null';
                    break;
                case self::TYPE_NUMBER:
                    $output .= 'null';
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= 'null';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= 'null';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_INTEGER:
                    $output .= $default . 'L';
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
                    $output .= 'null';
                    break;
            }
        }

        return $output;
    }

    /**
     * @param array $param
     * @param string $lang unused, always java
     * @return string
     */
    public function getParamExample(array $param, string $lang = 'java'): string
    {
        return parent::getParamExample($param, 'java');
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'       => 'default',
                'destination' => 'README.md',
                'template'    => '/java/README.md.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'CHANGELOG.md',
                'template'    => '/java/CHANGELOG.md.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'LICENSE.md',
                'template'    => '/java/LICENSE.md.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'pom.xml',
                'template'    => '/java/pom.xml.twig',
            ],
            [
                'scope'       => 'copy',
                'destination' => '.gitignore',
                'template'    => '/java/.gitignore',
            ],
            [
                'scope'       => 'method',
                'destination' => 'docs/examples/{{ service.name | caseLower }}/{{ method.name | caseKebab }}.md',
                'template'    => '/java/docs/example.md.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/Client.java',
                'template'    => '/java/src/main/java/io/appwrite/Client.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/Permission.java',
                'template'    => '/java/src/main/java/io/appwrite/Permission.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/Role.java',
                'template'    => '/java/src/main/java/io/appwrite/Role.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/ID.java',
                'template'    => '/java/src/main/java/io/appwrite/ID.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/Query.java',
                'template'    => '/java/src/main/java/io/appwrite/Query.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/Operator.java',
                'template'    => '/java/src/main/java/io/appwrite/Operator.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/exceptions/{{ spec.title | caseUcfirst }}Exception.java',
                'template'    => '/java/src/main/java/io/appwrite/exceptions/Exception.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/models/InputFile.java',
                'template'    => '/java/src/main/java/io/appwrite/models/InputFile.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/models/UploadProgress.java',
                'template'    => '/java/src/main/java/io/appwrite/models/UploadProgress.java.twig',
            ],
            [
                'scope'       => 'definition',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/models/{{ definition.name | caseUcfirst }}.java',
                'template'    => '/java/src/main/java/io/appwrite/models/Model.java.twig',
            ],
            [
                'scope'       => 'requestModel',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/models/{{ requestModel.name | caseUcfirst }}.java',
                'template'    => '/java/src/main/java/io/appwrite/models/RequestModel.java.twig',
            ],
            [
                'scope'       => 'enum',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/enums/{{ enum.name | caseUcfirst }}.java',
                'template'    => '/java/src/main/java/io/appwrite/enums/Enum.java.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/services/Service.java',
                'template'    => '/java/src/main/java/io/appwrite/services/Service.java.twig',
            ],
            [
                'scope'       => 'service',
                'destination' => '/src/main/java/{{ sdk.namespace | caseSlash }}/services/{{ service.name | caseUcfirst }}.java',
                'template'    => '/java/src/main/java/io/appwrite/services/ServiceTemplate.java.twig',
            ],
        ];
    }

    public function getFilters(): array
    {
        $parentFilters = parent::getFilters();

        // Replace returnType filter: use Map<String, Object> instead of bare T for generic models
        $overridden = [];
        foreach ($parentFilters as $filter) {
            if ($filter->getName() === 'returnType') {
                $overridden[] = new TwigFilter('returnType', function (array $method, array $spec, string $namespace, string $generic = 'Map<String, Object>') {
                    return $this->getReturnType($method, $spec, $namespace, $generic);
                });
            } else {
                $overridden[] = $filter;
            }
        }
        return $overridden;
    }
}
