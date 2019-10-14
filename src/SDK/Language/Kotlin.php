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

		]
	}

	/**
     * @param $type
     * @return string
     */
	public function getTypeName($type) 
	{

	}

	/**
     * @param array $param
     * @return string
     */
	public function getParamDefault(array $param)
	{

	}

	/**
     * @param array $param
     * @return string
     */
	public function getParamExample(array $param) 
	{

	}
}