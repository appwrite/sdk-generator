<?php

namespace Appwrite\SDK\Language;

class GraphQL extends HTTP
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GraphQL';
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        $type = '';

        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
                $type = 'Int';
                break;
            case self::TYPE_STRING:
                $type = 'String';
                break;
            case self::TYPE_FILE:
                $type = 'InputFile';
                break;
            case self::TYPE_BOOLEAN:
                $type = 'Bool';
                break;
            case self::TYPE_ARRAY:
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    $type = '[' . $this->getTypeName($parameter['array']) . ']';
                    break;
                }
                $type = '[String]';
                break;
            case self::TYPE_OBJECT:
                $type = 'JSON';
                break;
        }

        if (empty($type)) {
            $type = $parameter['type'];
        }

        if ($parameter['required'] ?? false) {
            $type .= '!';
        }

        return $type;
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

        $output = '';

        if (empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                    $output .= '0';
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= 'false';
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_STRING:
                    $output .= '""';
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_OBJECT:
                case self::TYPE_NUMBER:
                case self::TYPE_ARRAY:
                case self::TYPE_INTEGER:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= '"' . $default . '"';
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

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_BOOLEAN => 'false',
                self::TYPE_FILE => 'null',
                self::TYPE_INTEGER, self::TYPE_NUMBER => '0',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => '""',
            };
        }

        return match ($type) {
            self::TYPE_ARRAY, self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
                ? '"{}"'
                : '"' . str_replace('"', '\\"', json_encode(json_decode($example, true))) . '"',
            self::TYPE_STRING => '"' . $example . '"',
        };
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => '/graphql/docs/example.md.twig',
                'exclude'       => [
                    'services'  => [['name' => 'graphql']],
                    'methods'   => [['type' => 'webAuth']],
                ],
            ],
        ];
    }
}
