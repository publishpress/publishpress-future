<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@auto' => true,
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
            ->exclude([
                '.agents',
                '.cache',
                '.claude',
                '.codegraph',
                '.cursor',
                '.ddev',
                '.github',
                '.idea',
                '.opencode',
                '.vscode',
                '.web',
                '.wordpress-org',
                'bin',
                'cache',
                'dev-workspace',
                'dev-workspace-cache',
                'dist',
                'docs',
                'languages',
                'node_modules',
                'tests-old',
                'tests',
                'vendor',
            ])
    );
