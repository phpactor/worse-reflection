<?php

use PhpCsFixer\Config;

$finder = PhpCsFixer\Finder::create()
    ->in('lib')
    ->in('tests')
    ->exclude([
        'tests/Workspace',
    ])
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'no_unused_imports' => true,
        'phpdoc_to_property_type' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_trim' => true,
        'array_syntax' => ['syntax' => 'short'],
        'void_return' => true,
        'ordered_class_elements' => true,
        'single_quote' => true,
        'heredoc_indentation' => true,
        'global_namespace_import' => true,
    ])
    ->setFinder($finder)
;

