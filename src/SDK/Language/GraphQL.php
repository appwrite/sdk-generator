<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;

class GraphQL extends HTTP
{
    public function getName(): string
    {
        return 'GraphQL';
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return '"';
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($parameter);
        $type = match ($this->getSchemaType($parameter)) {
            self::TYPE_INTEGER => 'Int',
            self::TYPE_NUMBER => 'Float',
            self::TYPE_STRING => 'String',
            self::TYPE_FILE => 'InputFile',
            self::TYPE_BOOLEAN => 'Boolean',
            self::TYPE_ARRAY => '[' . $this->getTypeName($this->getArraySchema($parameter) ?? $schema) . ']',
            self::TYPE_OBJECT => 'JSON',
            default => 'JSON',
        };

        return $parameter instanceof Parameter && $parameter->required ? $type . '!' : $type;
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

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
                : '"' . str_replace('"', '\\"', json_encode(json_decode((string) $example, true))) . '"',
            self::TYPE_STRING => '"' . $example . '"',
        };
    }

    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
                'template'      => '/graphql/docs/example.md.twig',
                'exclude'       => [
                    'services'  => [['name' => 'graphql']],
                    'methods'   => [['type' => 'webAuth']],
                ],
            ],
        ];
    }
}
