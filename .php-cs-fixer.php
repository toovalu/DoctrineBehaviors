<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'config',
        'docs',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // '@Symfony' => true,
         '@PHP83Migration' => true,
        'backtick_to_shell_exec' => true,
         'declare_strict_types' => true,
        'increment_style' => ['style' => 'pre'],
        'native_function_invocation' => false,
        'array_indentation' => true,
        'self_static_accessor' => true,
        'blank_line_before_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'extra',
                'return',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block',
            ],
        ],
        'no_null_property_initialization' => false,
        'no_superfluous_phpdoc_tags' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_separation' => [
            'groups' => [
                ['deprecated', 'link', 'see', 'since'],
                ['author', 'copyright', 'license'],
                ['category', 'package', 'subpackage'],
                ['property', 'property-read', 'property-write'],
                ['ParamConverter', 'Entity', 'IsGranted'],
            ],
        ],
        'phpdoc_to_comment' => false,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder($finder);
