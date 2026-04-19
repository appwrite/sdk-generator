<?php

namespace Appwrite\SDK\Language;

class ClaudePlugin extends AgentSkills
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'ClaudePlugin';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $languages = [
            'typescript',
            'dart',
            'kotlin',
            'swift',
            'php',
            'python',
            'ruby',
            'go',
            'rust',
            'dotnet',
            'cli',
        ];

        $files = [];

        foreach ($languages as $lang) {
            $files[] = [
                'scope'       => 'default',
                'destination' => 'skills/' . $lang . '/SKILL.md',
                'template'    => 'claude-plugin/skills/' . $lang . '.md.twig',
            ];
        }

        $files[] = [
            'scope'       => 'default',
            'destination' => '.claude-plugin/plugin.json',
            'template'    => 'claude-plugin/plugin.json.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => '.claude-plugin/marketplace.json',
            'template'    => 'claude-plugin/marketplace.json.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'commands/deploy-site.md',
            'template'    => 'claude-plugin/commands/deploy-site.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'commands/deploy-function.md',
            'template'    => 'claude-plugin/commands/deploy-function.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => '.mcp.json',
            'template'    => 'claude-plugin/.mcp.json.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'README.md',
            'template'    => 'claude-plugin/README.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'CHANGELOG.md',
            'template'    => 'agent-skills/CHANGELOG.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'LICENSE',
            'template'    => 'cursor-plugin/LICENSE.twig',
        ];

        return $files;
    }
}
