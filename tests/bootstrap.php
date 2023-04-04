<?php

// normalize ES_VERSION for version_compare
if (isset($_SERVER['ES_VERSION']) && \str_contains($_SERVER['ES_VERSION'], 'SNAPSHOT')) {
    $_SERVER['ES_VERSION'] = \str_replace('-SNAPSHOT', '', $_SERVER['ES_VERSION']);
}

require_once __DIR__.'/../vendor/autoload.php';
