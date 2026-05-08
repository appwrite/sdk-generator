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

    public function getTitle(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getNamespace(): string
    {
        return '';
    }

    public function getVersion(): string
    {
        return '';
    }

    public function getEndpoint(): string
    {
        return '';
    }

    public function getEndpointDocs(): string
    {
        return '';
    }

    public function getLicenseName(): string
    {
        return '';
    }

    public function getLicenseURL(): string
    {
        return '';
    }

    public function getContactName(): string
    {
        return '';
    }

    public function getContactURL(): string
    {
        return '';
    }

    public function getContactEmail(): string
    {
        return '';
    }

    public function getServices(): array
    {
        return [];
    }

    public function getMethods($service): array
    {
        return [];
    }

    public function getTargetNamespace(array $method, string $service): string
    {
        return '';
    }

    public function getGlobalHeaders(): array
    {
        return [];
    }

    public function getDefinitions(): array
    {
        return [];
    }

    public function getRequestModels(): array
    {
        return [];
    }

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

    public function getRequestEnums(): array
    {
        return [];
    }

    public function getResponseEnums(): array
    {
        return [];
    }
}
