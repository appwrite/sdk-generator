<?php

namespace Appwrite\SDK\Language;

class REST extends HTTP
{
    /**
     * @return string
     */
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
                self::TYPE_FILE => 'cf 94 84 24 8d c4 91 10 0f dc 54 26 6c 8e 4b bc e8 ee 55 94 29 e7 94 89 19 26 28 01 26 29 3f 16...',
                self::TYPE_INTEGER, self::TYPE_NUMBER => '0',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => '""',
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => (function () use ($example) {
                // If array of strings, make sure any sub-strings are escaped
                if (\substr($example, 1, 1) === '"') {
                    $start = \substr($example, 0, 2);
                    $end = \substr($example, -2);
                    $contents = \substr($example, 2, -2);
                    $contents = \addslashes($contents);
                    return $start . $contents . $end;
                } else {
                    return $example;
                }
            })(),
            self::TYPE_FILE, self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
                ? '{}'
                : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                    ? (function () use ($formatted) {
                        // Replace leading four spaces with two spaces for indentation
                        $formatted = preg_replace('/^    /m', '  ', $formatted);
                        // Add two spaces before the closing brace if it's on a new line at the end
                        $formatted = preg_replace('/\n(?=[^}]*}$)/', "\n  ", $formatted);
                        return $formatted;
                    })()
                    : $example),
            self::TYPE_STRING => "\"{$example}\"",
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
            'template'      => '/rest/docs/example.md.twig',
          ],
        ];
    }
}
