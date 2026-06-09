<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\Config\RectorConfig;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Php85\Rector\Property\AddOverrideAttributeToOverriddenPropertiesRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([
        'rector.php',
        __DIR__ . '/example.php',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__ . '/vendor')
    ->withSkipPath(__DIR__ . '/tests/resources')
    ->withSkipPath(__DIR__ . '/tests/sdks/*')
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true
    )
    ->withSets([
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withSkip([
        ThrowWithPreviousExceptionRector::class,
        AddOverrideAttributeToOverriddenPropertiesRector::class,
        DisallowedEmptyRuleFixerRector::class,
        NewMethodCallWithoutParenthesesRector::class => [
            __DIR__ . '/tests/languages/php/test.php',
        ],
    ]);
