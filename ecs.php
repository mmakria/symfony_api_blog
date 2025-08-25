<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRules([
        NoUnusedImportsFixer::class,
    ])
    ->withPreparedSets(
        arrays: true,
        namespaces: true,
        spaces: true,
        docblocks: true,
        comments: true,
        psr12: true
    );