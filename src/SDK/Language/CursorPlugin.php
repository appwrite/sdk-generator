<?php

declare(strict_types=1);

namespace Appwrite\SDK\Language;

use Override;

class CursorPlugin extends AgentSkills
{
    #[Override]
    public function getName(): string
    {
        return 'CursorPlugin';
    }

    #[Override]
    public function getFiles(): array
    {
        $files = $this->getSkillFiles();

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
            'template'    => 'plugin/commands/deploy-site.md.twig',
        ];
        $files[] = [
            'scope'       => 'default',
            'destination' => 'commands/deploy-function.md',
            'template'    => 'plugin/commands/deploy-function.md.twig',
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
            'template'    => 'plugin/LICENSE.twig',
        ];

        return $files;
    }
}
