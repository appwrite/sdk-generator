<?php

namespace Appwrite\SDK;

abstract class Language {

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
    abstract public function getName();

    /**
     * @return array
     */
    abstract public function getKeywords();

    /**
     * @return array
     */
    abstract public function getIdentifierOverrides();

    /**
     * @return array
     */
    abstract public function getFiles();

    /**
     * @param array $parameter
     * @return string
     */
    abstract public function getTypeName(array $parameter): string;

    /**
     * @param array $param
     * @return string
     */
    abstract public function getParamDefault(array $param);

    /**
     * @param array $param
     * @return string
     */
    abstract public function getParamExample(array $param);

    /**
     * @param string $key
     * @param string $value
     * @return Language
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
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

    protected function toUpperCase(string $value): string
    {
        return ucfirst($this->helperCamelCase($value));
    }

    protected function helperCamelCase($str): string
    {
        $str = preg_replace('/[^a-z0-9' . implode("", []) . ']+/i', ' ', $str);
        $str = trim($str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);

        return lcfirst($str);
    }
}