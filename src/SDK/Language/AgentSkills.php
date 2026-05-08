<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;
use Twig\TwigFilter;

class AgentSkills extends Language
{
    protected array $skillLanguages = [
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

    protected string $skillDestination = 'skills/{{ spec.title | caseLower }}-%s/SKILL.md';
    protected bool $prefixSkillName = true;

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
    public function getFilters(): array
    {
        return [
            new TwigFilter('skillName', function (string $lang, array $spec): string {
                return $this->getSkillName($lang, $spec['title'] ?? '');
            }),
        ];
    }

    protected function getSkillName(string $lang, string $specTitle): string
    {
        if (!$this->prefixSkillName) {
            return $lang;
        }

        return \strtolower($specTitle) . '-' . $lang;
    }

    protected function getSkillFiles(): array
    {
        $files = [];

        foreach ($this->skillLanguages as $lang) {
            $files[] = [
                'scope'       => 'default',
                'destination' => \sprintf($this->skillDestination, $lang),
                'template'    => 'agent-skills/' . $lang . '.md.twig',
            ];
        }

        return $files;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $files = $this->getSkillFiles();

        $files[] = [
            'scope'       => 'default',
            'destination' => 'README.md',
            'template'    => 'agent-skills/README.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'CHANGELOG.md',
            'template'    => 'agent-skills/CHANGELOG.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'LICENSE',
            'template'    => 'agent-skills/LICENSE.twig',
        ];

        return $files;
    }
}
