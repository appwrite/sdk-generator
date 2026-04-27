<?php

namespace Appwrite\SDK\Language;

class CodexPlugin extends AgentSkills
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CodexPlugin';
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
                'destination' => 'skills/{{ spec.title | caseLower }}-' . $lang . '/SKILL.md',
                'template'    => 'codex-plugin/skills/' . $lang . '.md.twig',
            ];
        }

        $files[] = [
            'scope'       => 'default',
            'destination' => '.codex-plugin/plugin.json',
            'template'    => 'codex-plugin/plugin.json.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => '.mcp.json',
            'template'    => 'codex-plugin/.mcp.json.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'skills/{{ spec.title | caseLower }}-deploy-site/SKILL.md',
            'template'    => 'codex-plugin/skills/deploy-site.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'skills/{{ spec.title | caseLower }}-deploy-function/SKILL.md',
            'template'    => 'codex-plugin/skills/deploy-function.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'config.toml',
            'template'    => 'codex-plugin/config.toml.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'README.md',
            'template'    => 'codex-plugin/README.md.twig',
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
