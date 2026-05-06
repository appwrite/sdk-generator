<?php

namespace Appwrite\SDK\Language;

class ClaudePlugin extends AgentSkills
{
    protected string $skillDestination = 'skills/%s/SKILL.md';
    protected bool $prefixSkillName = false;

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
        $files = $this->getSkillFiles();

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
            'template'    => 'plugin/commands/deploy-site.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'commands/deploy-function.md',
            'template'    => 'plugin/commands/deploy-function.md.twig',
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
            'template'    => 'plugin/LICENSE.twig',
        ];

        return $files;
    }
}
