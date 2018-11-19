<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(['lib', 'test']);

$config = Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        // [contrib] Multi-line whitespace before closing semicolon are prohibited.
        'multiline_spaces_before_semicolon',
        // [contrib] There should be no blank lines before a namespace declaration.
        'no_blank_lines_before_namespace',
        // [contrib] Ordering use statements.
        'ordered_use',
        // [contrib] Annotations should be ordered so that param annotations come first, then throws annotations, then return annotations.
        'phpdoc_order',
        // [contrib] Arrays should use the short syntax.
        'short_array_syntax',
        // [contrib] Ensure there is no code on the same line as the PHP open tag.
        'newline_after_open_tag',
        // [contrib] Use null coalescing operator ?? where possible
        'ternary_to_null_coalescing',
        // [contrib] There should not be useless else cases.
        'no_useless_else',
        // [contrib] Use dedicated PHPUnit assertions for better error messages.
        '@PHPUnit60Migration:risky' => true,
        'php_unit_dedicate_assert' => ['target' => 'newest'],
    ])
    ->finder($finder);

return $config;
