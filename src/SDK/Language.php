<?php

namespace Appwrite\SDK;

use Normalizer;

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

    function toSnakeCase(string $str): string {
        // Replace alternative character sets
        $str = Normalizer::normalize($str, Normalizer::FORM_D);
    
        // Replace seperating characters with underscores
        // Includes: spaces, dashes, apostrophes, periods and slashes
        $str = preg_replace('/[ \'.\/-]/', '_', $str);
    
        // Seperate camelCase with underscores
        $str = preg_replace_callback('/([a-z])([^a-z_])/', function ($matches) {
            return $matches[1] . '_' . strtolower($matches[2]);
        }, $str);
    
        // Remove ignorable characters
        return preg_replace('/[^a-z0-9_]/', '', strtolower($str));
    }

    function toUpperSnakeCase(string $str): string
    {
        $str = $this->toSnakeCase($str);
        return \strtoupper($str);
    }

    function toKebabCase(string $str): string
    {
        $str = $this->toSnakeCase($str);
        return str_replace('_', '-', $str);
    }

    function toDotCase(string $str): string
    {
        $str = $this->toSnakeCase($str);
        return str_replace('_', '.', $str);
    }

    function toSlashCase(string $str): string
    {
        $str = $this->toSnakeCase($str);
        return str_replace('_', '/', $str);
    }

    function toPascalCase(string $value): string
    {
        $str = $this->toSnakeCase($value);
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    function toCamelCase(string $str): string
    {
        $str = $this->toPascalCase($str);
        return lcfirst($str);
    }
}
