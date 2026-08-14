<?php

namespace Appwrite\SDK\Language;

use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Specification;
use Override;
use Appwrite\SDK\Language;
use Twig\TwigFilter;

class Skills extends Language
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

    protected string $skillDestination = 'skills/{{ spec.info.title | caseLower }}-%s/SKILL.md';
    protected bool $prefixSkillName = true;

    public function getName(): string
    {
        return 'Skills';
    }

    public function getKeywords(): array
    {
        return [];
    }

    public function getIdentifierOverrides(): array
    {
        return [];
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return '"';
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string
    {
        return $this->getSchemaType($parameter);
    }

    public function getParamDefault(Schema|Parameter $param): string
    {
        return $this->getSchemaDefault($param);
    }

    public function getParamExample(Schema|Parameter $param, string $lang = ''): string
    {
        return $this->getSchemaExample($param);
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('skillName', fn(string $lang, Specification $spec): string => $this->getSkillName($lang, $spec->info->title)),
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
                'template'    => 'skills/' . $lang . '.md.twig',
            ];
        }

        return $files;
    }

    public function getFiles(): array
    {
        $files = $this->getSkillFiles();

        $files[] = [
            'scope'       => 'default',
            'destination' => 'README.md',
            'template'    => 'skills/README.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'CHANGELOG.md',
            'template'    => 'skills/CHANGELOG.md.twig',
        ];

        $files[] = [
            'scope'       => 'default',
            'destination' => 'LICENSE',
            'template'    => 'skills/LICENSE.twig',
        ];

        return $files;
    }
}
