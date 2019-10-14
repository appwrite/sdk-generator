<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Kotlin extends Language {

	/**
	 * @return @string
	 **/
	public function getName() 
	{
		return 'Kotlin'
	}

	/**
     * @return array
     */
	public function getKeywords() 
	{
		return [
			'as',
			'break', 
			'class',
			'continue', 
			'do', 
			'else', 
			'false',
			'for',
			'fun', 
			'if', 
			'in',
			'interface',
			'is',
			'null',
			'object', 
			'package', 
			'return', 
			'super',
			'this',
			'throw',
			'true',
			'try',
			'typealias', 
			'typeof', 
			'val', 
			'var', 
			'when', 
			'while',
			'by',
			'catch',
			'constructor',
			'delegate',
			'dynamic',
			'field',
			'file',
			'finally',
			'get',
			'import',
			'init',
			'param',
			'property',
			'receiver',
			'set',
			'setparam',
			'where',
			'actual',
			'abstract',
			'annotation',
			'companion',
			'const',
			'crossinline',
			'data',
			'enum',
			'expect',
			'external',
			'final',
			'infix',
			'inline',
			'inner',
			'internal',
			'lateinit',
			'noinline',
			'open',
			'operator',
			'out',
			'override',
			'private',
			'protected',
			'public',
			'reified',
			'sealed',
			'suspend',
			'tailrec',
			'vararg',
			'field',
			'it'
		];
	}

	/**
     * @return array
     */
	public function getFiles(): Array 
	{
		return [

		];
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
            case self::TYPE_FILE:
                return 'File';
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
		 $type       = (isset($param['type'])) ? $param['type'] : '';
        $default    = (isset($param['default'])) ? $param['default'] : '';
        $required   = (isset($param['required'])) ? $param['required'] : '';

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
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
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

	/**
     * @param array $param
     * @return string
     */
	public function getParamExample(array $param) 
	{
		$type       = (isset($param['type'])) ? $param['type'] : '';
        $example    = (isset($param['example'])) ? $param['example'] : '';

        $output = '';

        if(empty($example) && $example !== 0 && $example !== false) {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_BOOLEAN:
                    $output .= 'None';
                    break;
                case self::TYPE_STRING:
                    $output .= "''";
                    break;
                case self::TYPE_ARRAY:
                    $output .= '[]';
                    break;
                case self::TYPE_FILE:
                    $output .= "document.getElementById('uploader').files[0]";
                    break;
            }
        }
        else {
            switch ($type) {
                case self::TYPE_NUMBER:
                case self::TYPE_INTEGER:
                case self::TYPE_ARRAY:
                    $output .= $example;
                    break;
                case self::TYPE_BOOLEAN:
                    $output .= ($example) ? 'True' : 'False';
                    break;
                case self::TYPE_STRING:
                    $output .= "'{$example}'";
                    break;
                case self::TYPE_FILE:
                    $output .= "document.getElementById('uploader').files[0]"; //TODO add file class
                    break;
            }
        }

        return $output;
	}
}