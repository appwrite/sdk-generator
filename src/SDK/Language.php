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
    public const TYPE_PAYLOAD = 'payload';

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

    protected function toPascalCase($str): string
    {
        // Normalize the string to decompose accented characters
        $str = \Normalizer::normalize($str, \Normalizer::FORM_D);

        // Remove accents and other residual non-ASCII characters
        $str = \preg_replace('/\p{M}/u', '', $str);

        // Insert spaces before uppercase letters where appropriate
        $str = \preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', $str);
        $str = \preg_replace('/(?<=[A-Z])(?=[A-Z][a-z])/', ' ', $str);

        // Replace any sequence of non-alphanumeric characters with a space
        $str = \preg_replace('/[^a-zA-Z0-9]+/', ' ', $str);
        $str = \trim($str);
        $str = strtolower($str);

        // Uppercase the first letter of each word, then remove spaces
        $str = \ucwords($str);
        return \str_replace(' ', '', $str);
    }

    protected function toCamelCase($str): string
    {
        return \lcfirst($this->toPascalCase($str));
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
}
