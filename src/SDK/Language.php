<?php

namespace Appwrite\SDK;

abstract class Language
{
    const TYPE_INTEGER = 'integer';
    const TYPE_NUMBER = 'number';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_FILE = 'file';

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
     * @param $type
     * @return string
     */
    abstract public function getTypeName($type);

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
}
