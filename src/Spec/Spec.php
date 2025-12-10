<?php

namespace Appwrite\Spec;

use Exception;
use ArrayObject;

abstract class Spec extends ArrayObject
{
    private const SET_TYPE_ASSIGN   = 'assign';
    private const SET_TYPE_PREPEND  = 'prepend';
    private const SET_TYPE_APPEND   = 'append';

    /**
     * Spec constructor.
     * @param string $input
     * @throws Exception
     */
    public function __construct($input)
    {
        if (\filter_var($input, FILTER_VALIDATE_URL)) {
            $data = \file_get_contents($input, false, \stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'allow_self_signed' => true,
                ]
            ]));

            if (!$data) {
                throw new Exception('Can\'t read data from ' . $input);
            }

            $input = $data;
        }

        try {
            $input = json_decode($input, true);
        } catch (Exception $e) {
            throw new Exception('Failed to parse spec: ' . $e->getMessage());
        }

        parent::__construct($input);
    }

    /**
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * @return string
     */
    abstract public function getNamespace(): string;

    /**
     * @return string
     */
    abstract public function getVersion(): string;

    /**
     * @return string
     */
    abstract public function getEndpoint(): string;

    /**
     * @return string
     */
    abstract public function getEndpointDocs(): string;

    /**
     * @return string
     */
    abstract public function getLicenseName(): string;

    /**
     * @return string
     */
    abstract public function getLicenseURL(): string;

    /**
     * @return string
     */
    abstract public function getContactName(): string;

    /**
     * @return string
     */
    abstract public function getContactURL(): string;

    /**
     * @return string
     */
    abstract public function getContactEmail(): string;

    /**
     * @return array
     */
    abstract public function getServices(): array;

    /**
     * @param string $service
     * @return array
     */
    abstract public function getMethods($service): array;

    /**
     * @param array $method
     * @param string $service
     * @return string
     */
    abstract public function getTargetNamespace(array $method, string $service): string;

    /**
     * @return string
     */
    abstract public function getGlobalHeaders(): array;

    /**
     * @return array
     */
    abstract public function getDefinitions(): array;

    /**
     * Get request model definitions
     *
     * @return array
     */
    abstract public function getRequestModels(): array;

    /**
     * Get Attribute
     *
     * Method for getting a specific fields attribute. If $name is not found $default value will be returned.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null): mixed
    {
        $name = explode('.', $name);

        $temp = &$this;

        foreach ($name as $key) {
            if (!isset($temp[$key])) {
                return $default;
            }

            $temp = &$temp[$key];
        }

        return $temp;
    }

    /**
     * Set Attribute
     *
     * Method for setting a specific field attribute
     *
     * @param string $key
     * @param mixed $value
     * @param array $parameter
     * @return mixed
     */
    public function setAttribute(string $key, mixed $value, $type = self::SET_TYPE_ASSIGN): mixed
    {
        switch ($type) {
            case self::SET_TYPE_ASSIGN:
                $this[$key] = $value;
                break;
            case self::SET_TYPE_APPEND:
                $this[$key] = (!isset($this[$key]) || !is_array($this[$key])) ? [] : $this[$key];
                $this[$key][] = $value;
                break;
            case self::SET_TYPE_PREPEND:
                $this[$key] = (!isset($this[$key]) || !is_array($this[$key])) ? [] : $this[$key];
                array_unshift($this[$key], $value);
                break;
        }

        return $this;
    }

    /**
     * Get Request Enums
     *
     * @return array
     */
    abstract public function getRequestEnums(): array;

    /**
     * Get Response Enums
     *
     * @return array
     */
    abstract public function getResponseEnums(): array;
}
