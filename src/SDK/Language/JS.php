<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

abstract class JS extends Language {

    protected $params = [
        'npmPackage' => 'packageName',
        'bowerPackage' => 'packageName',
    ];

    /**
     * @param string $name
     * @return $this
     */
    public function setNPMPackage($name)
    {
        $this->setParam('npmPackage', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setBowerPackage($name)
    {
        $this->setParam('bowerPackage', $name);

        return $this;
    }

    /**
     * Get Language Keywords List
     *
     * @return array
     */
    public function getKeywords()
    {
        return [
            'abstract',
			'arguments',
			'await', // new in ECMAScript 5 and 6.
			'boolean',
			'break',
			'byte',
			'case',
			'catch',
			'char',
			'class', // new in ECMAScript 5 and 6.
			'const',
			'continue',
			'debugger',
			'default',
			'delete',
			'do',
			'double',
			'else',
			'enum', // new in ECMAScript 5 and 6.
			'eval',
			'export', // new in ECMAScript 5 and 6.
			'extends', // new in ECMAScript 5 and 6.
			'false',
			'final',
			'finally',
			'float',
			'for',
			'function',
			'goto',
			'if',
			'implements',
			'import', // new in ECMAScript 5 and 6.
			'in',
			'instanceof',
			'int',
			'interface',
			'let', // new in ECMAScript 5 and 6.
			'long',
			'native',
			'new',
			'null',
			'package',
			'private',
			'protected',
			'public',
			'return',
			'short',
			'static',
			'super', // new in ECMAScript 5 and 6.
			'switch',
			'synchronized',
			'this',
			'throw',
			'throws',
			'transient',
			'true',
			'try',
			'typeof',
			'var',
			'void',
			'volatile',
			'while',
			'with',
			'yield',
        ];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides()
    {
        return [];
    }

    /**
     * @param $type
     * @return string
     */
    public function getTypeName($type)
    {
        switch ($type) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                return 'number';
            break;
            case self::TYPE_ARRAY:
                return 'string[]';
            case self::TYPE_FILE:
                return 'string';
            break;
        }

        return $type;
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param)
    {
        $type       = $param['type'] ?? '';
        $default    = $param['default'] ?? '';
        $required   = $param['required'] ?? '';

        if($required) {
            return '';
        }

        $output = ' = ';

        if(empty($default) && $default !== 0 && $default !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'null';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_OBJECT:
                    $output .= '{}';
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                case self::TYPE_OBJECT:
                    $output .= $default;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($default) ? 'true' : 'false';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$default}'";
                    break;
            }
        }

        return $output;
    }
}
