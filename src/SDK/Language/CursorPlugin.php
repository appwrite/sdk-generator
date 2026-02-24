<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class CursorPlugin extends Language
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CursorPlugin';
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
        // Reuse the same language skills from agent-skills SDK
        $languages = [
            'typescript',
            'dart',
            'kotlin',
            'swift',
            'php',
            'python',
            'ruby',
            'go',
            'dotnet',
        ];

        $files = [];

        // Skills — reuse agent-skills templates
        foreach ($languages as $lang) {
            $files[] = [
                'scope'       => 'default',
                'destination' => 'skills/{{ spec.title | caseLower }}-' . $lang . '/SKILL.md',
                'template'    => 'agent-skills/' . $lang . '.md.twig',
            ];
        }

        // Logo
        $files[] = [
            'scope'       => 'copy',
            'destination' => 'logo.svg',
            'template'    => 'cursor-plugin/logo.svg',
        ];

        // Plugin manifest
        $files[] = [
            'scope'       => 'default',
            'destination' => '.cursor-plugin/plugin.json',
            'template'    => 'cursor-plugin/plugin.json.twig',
        ];

        // Commands
        $files[] = [
            'scope'       => 'default',
            'destination' => 'commands/deploy-site.md',
            'template'    => 'cursor-plugin/commands/deploy-site.md.twig',
        ];
        $files[] = [
            'scope'       => 'default',
            'destination' => 'commands/deploy-function.md',
            'template'    => 'cursor-plugin/commands/deploy-function.md.twig',
        ];

        // MCP server definitions
        $files[] = [
            'scope'       => 'default',
            'destination' => '.mcp.json',
            'template'    => 'cursor-plugin/.mcp.json.twig',
        ];

        // README, CHANGELOG, LICENSE
        $files[] = [
            'scope'       => 'default',
            'destination' => 'README.md',
            'template'    => 'cursor-plugin/README.md.twig',
        ];
        $files[] = [
            'scope'       => 'default',
            'destination' => 'CHANGELOG.md',
            'template'    => 'cursor-plugin/CHANGELOG.md.twig',
        ];
        $files[] = [
            'scope'       => 'default',
            'destination' => 'LICENSE',
            'template'    => 'cursor-plugin/LICENSE.twig',
        ];

        return $files;
    }
}
