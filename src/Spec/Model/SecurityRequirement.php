<?php

declare(strict_types=1);

namespace Appwrite\Spec\Model;

final readonly class SecurityRequirement
{
    /**
     * @param array<string, list<string>> $schemes
     */
    public function __construct(public array $schemes)
    {
    }
}
