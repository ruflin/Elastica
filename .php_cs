<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(array('lib', 'test'));

$config = Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(array(
        // [contrib] Multi-line whitespace before closing semicolon are prohibited.
        'multiline_spaces_before_semicolon',
        // [contrib] There should be no blank lines before a namespace declaration.
        'no_blank_lines_before_namespace',
        // [contrib] Ordering use statements.
        'ordered_use',
    ))
    ->finder($finder);

return $config;
