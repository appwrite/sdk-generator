<?php

declare(strict_types=1);

namespace Appwrite\SDK\Language;

use Override;

class ClaudePlugin extends AgentSkills
{
    #[Override]
    protected string $skillDestination = 'skills/%s/SKILL.md';
    #[Override]
    protected bool $prefixSkillName = false;

    #[Override]
    public function getName(): string
    {
        return 'ClaudePlugin';
    }

    #[Override]
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
            'template'    => 'skills/CHANGELOG.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'LICENSE',
            'template'    => 'plugin/LICENSE.twig',
        ];

        return $files;
    }
}
