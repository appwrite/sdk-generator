<?php

namespace Appwrite\SDK;

abstract class Language
{
    public const TYPE_INTEGER = 'integer';
    public const TYPE_NUMBER = 'number';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_ARRAY = 'array';
    public const TYPE_OBJECT = 'object';
    public const TYPE_FILE = 'file';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return array<string>
     */
    abstract public function getKeywords(): array;

    /**
     * @return array<string>
     */
    abstract public function getIdentifierOverrides(): array;

    /**
     * @return array<array>
     */
    abstract public function getFiles(): array;

    /**
     * @param array $parameter
     * @return string
     */
    abstract public function getTypeName(array $parameter, array $spec = []): string;

    /**
     * @param array $param
     * @return string
     */
    abstract public function getParamDefault(array $param): string;

    /**
     * @param array $param
     * @return string
     */
    abstract public function getParamExample(array $param): string;

    /**
     * @param string $key
     * @param string $value
     * @return Language
     */
    public function setParam(string $key, string $value): Language
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Language specific filters.
     * @return array
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * Language specific functions.
     * @return array
     */
    public function getFunctions(): array
    {
        return [];
    }

    protected function toPascalCase(string $value): string
    {
        return \ucfirst($this->toCamelCase($value));
    }

    protected function toCamelCase($str): string
    {
        // Normalize the string to decompose accented characters
        $str = \Normalizer::normalize($str, \Normalizer::FORM_D);

        // Remove accents and other residual non-ASCII characters
        $str = \preg_replace('/\p{M}/u', '', $str);

        $str = \preg_replace('/[^a-zA-Z0-9]+/', ' ', $str);
        $str = \trim($str);
        $str = \ucwords($str);
        $str = \str_replace(' ', '', $str);
        $str = \lcfirst($str);

        return $str;
    }

    protected function toSnakeCase($str): string
    {
        // Normalize the string to decompose accented characters
        $str = \Normalizer::normalize($str, \Normalizer::FORM_D);

        // Remove accents and other residual non-ASCII characters
        $str = \preg_replace('/\p{M}/u', '', $str);

        // Remove apostrophes before replacing non-word characters with underscores
        $str = \str_replace("'", '', $str);
        $str = \preg_replace('/[^a-zA-Z0-9]+/', '_', $str);
        $str = \preg_replace('/_+/', '_', $str);
        $str = \trim($str, '_');
        $str = \strtolower($str);

        return $str;
    }

    protected function toUpperSnakeCase($str): string
    {
        return \strtoupper($this->toSnakeCase($str));
    }

    public function isPermissionString(string $string): bool
    {
        $pattern = '/^\[("(read|update|delete|write)\(\\"[^\\"]+\\"\)"(,\s*)?)+\]$/';
        return preg_match($pattern, $string) === 1;
    }

    public function extractPermissionParts(string $string): array
    {
        $inner = substr($string, 1, -1);
        preg_match_all('/"(read|update|delete|write)\(\\"([^\\"]+)\\"\)"/', $inner, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $action = $match[1];
            $roleString = $match[2];
            
            $role = null;
            $id = null;
            $innerRole = null;

            if (strpos($roleString, ':') !== false) {
                $role = explode(':', $roleString, 2)[0];
                $idString = explode(':', $roleString, 2)[1];

                if (strpos($idString, '/') !== false) {
                    $id = explode('/', $idString, 2)[0];
                    $innerRole = explode('/', $idString, 2)[1];
                }
            } else {
                $role = $roleString;
            }

            $result[] = [
                'action' => $action,
                'role' => $role,
                'id' => $id ?? null,
                'innerRole' => $innerRole
            ];
        }

        return $result;
    }

    public function hasPermissionParam(array $parameters): bool
    {
        foreach ($parameters as $param) {
            $example = $param['example'] ?? '';
            if (!empty($example) && is_string($example) && $this->isPermissionString($example)) {
                return true;
            }
        }
        return false;
    }
}
