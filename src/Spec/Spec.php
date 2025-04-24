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
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $data = file_get_contents($input, false, stream_context_create([
                "ssl" => ['verify_peer' => false, 'allow_self_signed' => true]
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
    abstract public function getTitle();

    /**
     * @return string
     */
    abstract public function getDescription();

    /**
     * @return string
     */
    abstract public function getNamespace();

    /**
     * @return string
     */
    abstract public function getVersion();

    /**
     * @return string
     */
    abstract public function getEndpoint();

    /**
     * @return string
     */
    abstract public function getEndpointDocs();

    /**
     * @return string
     */
    abstract public function getLicenseName();

    /**
     * @return string
     */
    abstract public function getLicenseURL();

    /**
     * @return string
     */
    abstract public function getContactName();

    /**
     * @return string
     */
    abstract public function getContactURL();

    /**
     * @return string
     */
    abstract public function getContactEmail();

    /**
     * @return array
     */
    abstract public function getServices();

    /**
     * @param string $service
     * @return array
     */
    abstract public function getMethods($service);

    /**
     * @return string
     */
    abstract public function getGlobalHeaders();

    /**
     * @return array
     */
    abstract public function getDefinitions();

    /**
     * Get Attribute
     *
     * Method for getting a specific fields attribute. If $name is not found $default value will be returned.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
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
     * @param  string $key
     * @param  mixed $value
     * @param array $parameter
     * @return mixed
     */
    public function setAttribute($key, $value, $type = self::SET_TYPE_ASSIGN)
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
     * Get Enums
     *
     * @return array
     */
    abstract public function getEnums();
}
