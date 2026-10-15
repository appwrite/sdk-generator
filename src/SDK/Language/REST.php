<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;

class REST extends HTTP
{
    public function getName(): string
    {
        return 'REST';
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

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        $type       = $this->getSchemaType($param);
        $example    = $this->getSchemaExample($param);

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_BOOLEAN => 'false',
                self::TYPE_FILE => 'cf 94 84 24 8d c4 91 10 0f dc 54 26 6c 8e 4b bc e8 ee 55 94 29 e7 94 89 19 26 28 01 26 29 3f 16...',
                self::TYPE_INTEGER, self::TYPE_NUMBER => '0',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => '""',
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $example,
            self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
                ? '{}'
                : (($formatted = json_encode(json_decode((string) $example, true), JSON_PRETTY_PRINT))
                    ? (function () use ($formatted): string|array|null {
                        // Replace leading four spaces with two spaces for indentation
                        $formatted = preg_replace('/^    /m', '  ', $formatted);
                        // Add two spaces before the closing brace if it's on a new line at the end
                        $formatted = preg_replace('/\n(?=[^}]*}$)/', "\n  ", (string) $formatted);
                        return $formatted;
                    })()
                    : $example),
            self::TYPE_STRING => "\"{$example}\"",
        };
    }

    public function getFiles(): array
    {
        return [
          [
            'scope'         => 'method',
            'destination'   => 'docs/examples/{{service.name | caseLower}}/{{(method | methodName) | caseKebab}}.md',
            'template'      => '/rest/docs/example.md.twig',
          ],
        ];
    }
}
