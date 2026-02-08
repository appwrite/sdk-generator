<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class AgentSkills extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'AgentSkills';
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getIdentifierOverrides(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    /**
     * @return string
     */
    public function getStringQuote(): string
    {
        return '"';
    }

    /**
     * @param string $elements
     * @return string
     */
    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    /**
     * @param array $parameter
     * @param array $spec
     * @return string
     */
    public function getTypeName(array $parameter, array $spec = []): string
    {
        return $parameter['type'] ?? 'string';
    }

    /**
     * @param array $param
     * @return string
     */
    public function getParamDefault(array $param): string
    {
        return $param['default'] ?? '';
    }

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
    {
        return $param['example'] ?? '';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $languages = [
            'web',
            'node',
            'react-native',
            'deno',
            'flutter',
            'dart',
            'android',
            'apple',
            'kotlin',
            'swift',
            'php',
            'python',
            'ruby',
            'go',
            'dotnet',
            'cli',
        ];

        $files = [];

        foreach ($languages as $lang) {
            $files[] = [
                'scope'       => 'default',
                'destination' => '{{ spec.title | caseLower }}-' . $lang . '/SKILL.md',
                'template'    => 'agent-skills/' . $lang . '.md.twig',
            ];
        }

        return $files;
    }
}
