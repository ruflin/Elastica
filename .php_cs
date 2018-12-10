<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__])
    ->exclude(['vendor', 'var'])
    ->notPath('/cache/')
;

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'psr0' => false,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'blank_line_after_opening_tag' => false,
        'lowercase_cast' => true,
        'lowercase_constants' => true,
        'lowercase_keywords' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'native_function_invocation' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'php_unit_dedicate_assert' => ['target' => 'newest'],
        'ternary_to_null_coalescing' => true,
        'phpdoc_order' => true,
    ])
;
