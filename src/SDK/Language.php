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

    protected function toPascalCase(string $value): string
    {
        return ucfirst($this->toCamelCase($value));
    }

    protected function toCamelCase($str): string
    {
        $str = preg_replace('/[^a-z0-9' . implode("", []) . ']+/i', ' ', $str);
        $str = trim($str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        return lcfirst($str);
    }

    protected function toSnakeCase($str): string 
    {
        // Replace alternative character sets
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);

        // Replace seperating characters with underscores
        // Includes: spaces, dashes, apostrophes, periods and slashes
        $str = preg_replace('/[ \'.\/-]/', '_', $str);

        // Seperate camelCase with underscores
        $str = preg_replace_callback('/([a-z])([^a-z_])/', function($matches) {
            return $matches[1] . '_' . strtolower($matches[2]);
        }, $str);

        // Remove ignorable characters
        return preg_replace('/[^a-z0-9_]/', '', strtolower($str));
    }

    protected function toUpperSnakeCase($str): string
    {
        return \strtoupper($this->toSnakeCase($str));
    }
}
