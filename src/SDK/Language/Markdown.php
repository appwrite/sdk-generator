<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language\JS;
use Twig\TwigFilter;

class Markdown extends JS
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Markdown';
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        // Markdown doesn't need keyword escaping
        return [];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [];
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return "'";
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            // GitHub workflows
            [
                'scope'       => 'default',
                'destination' => '.github/workflows/publish.yml',
                'template'    => 'markdown/.github/workflows/publish.yml.twig',
            ],
            // Package configuration
            [
                'scope'       => 'default',
                'destination' => 'package.json',
                'template'    => 'markdown/package.json.twig',
            ],
            [
                'scope'       => 'default',
                'destination' => 'README.md',
                'template'    => 'markdown/README.md.twig',
            ],
            [
                'scope'       => 'copy',
                'destination' => 'tsconfig.json',
                'template'    => 'markdown/tsconfig.json',
            ],
            // Source files (static - just copy)
            [
                'scope'       => 'copy',
                'destination' => 'src/types.ts',
                'template'    => 'markdown/src/types.ts',
            ],
            [
                'scope'       => 'copy',
                'destination' => 'src/index.ts',
                'template'    => 'markdown/src/index.ts',
            ],
            // Manifest - needs templating for TOC generation
            [
                'scope'       => 'copy',
                'destination' => 'src/manifest.ts',
                'template'    => 'markdown/src/manifest.ts',
            ],
            // Build scripts (static - just copy)
            [
                'scope'       => 'copy',
                'destination' => 'scripts/build-manifest.ts',
                'template'    => 'markdown/scripts/build-manifest.ts',
            ],
            // Documentation markdown files
            [
                'scope'       => 'method',
                'destination' => 'docs/typescript/{{ service.name | caseLower }}/{{ method.name | caseKebab }}.md',
                'template'    => 'markdown/typescript/method.md.twig',
            ],
        ];
    }

    /**
     * @param array $parameter
     * @param array $method
     * @return string
     */
    public function getTypeName(array $parameter, array $method = []): string
    {
        // For TypeScript/JavaScript-like languages
        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        if (isset($parameter['items'])) {
            $parameter['array'] = $parameter['items'];
        }
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                return 'number';
            case self::TYPE_ARRAY:
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'any[]';
            case self::TYPE_FILE:
                return 'File';
            case self::TYPE_OBJECT:
                return 'object';
        }
        return $parameter['type'];
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        $type = $param['type'] ?? '';
        $default = $param['default'] ?? '';
        $required = $param['required'] ?? false;

        if ($required) {
            return '';
        }

        if (!empty($default)) {
            return ' = ' . $default;
        }

        return match ($type) {
            self::TYPE_ARRAY => ' = []',
            self::TYPE_OBJECT => ' = {}',
            default => ' = null',
        };
    }

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
    {
        $type = $param['type'] ?? '';
        $example = $param['example'] ?? '';

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => 'file',
                self::TYPE_INTEGER, self::TYPE_NUMBER => '0',
                self::TYPE_BOOLEAN => 'false',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_INTEGER, self::TYPE_NUMBER => (string)$example,
            self::TYPE_FILE => 'file',
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
                ? '{}'
                : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                    ? $formatted
                    : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }

    public function getPermissionExample(string $example): string
    {
        $permissions = $this->extractPermissionParts($example);
        $result = [];

        foreach ($permissions as $permission) {
            $action = ucfirst($permission['action']);
            $role = ucfirst($this->toCamelCase($permission['role']));

            if ($permission['id'] !== null) {
                if ($permission['innerRole'] !== null) {
                    $result[] = "Permission.{$action}(Role.{$role}('{$permission['id']}', '{$permission['innerRole']}'))";
                } else {
                    $result[] = "Permission.{$action}(Role.{$role}('{$permission['id']}'))";
                }
            } else {
                $result[] = "Permission.{$action}(Role.{$role}())";
            }
        }

        return '[' . implode(', ', $result) . ']';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('getPropertyType', function ($value, $method = []) {
                return $this->getTypeName($value, $method);
            }),
            new TwigFilter('comment', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = wordwrap($line, 80, "\n");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('caseEnumKey', function (string $value) {
                return $this->toPascalCase($value);
            }),
            new TwigFilter('getResponseModel', function (array $method) {
                if (!empty($method['responseModel']) && $method['responseModel'] !== 'any') {
                    return 'Models.' . \ucfirst($method['responseModel']);
                }
                return null;
            }),
            new TwigFilter('needsPermissionImport', function (array $parameters) {
                foreach ($parameters as $param) {
                    if (($param['type'] ?? '') === self::TYPE_ARRAY) {
                        $example = $param['example'] ?? '';
                        if ($this->isPermissionString($example)) {
                            return true;
                        }
                    }
                }
                return false;
            }),
            new TwigFilter('decodeHtmlEntities', function (string $value) {
                return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }),
        ];
    }
}
