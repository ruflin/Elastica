<?php

declare(strict_types=1);

// normalize ES_VERSION for version_compare
if (isset($_SERVER['ES_VERSION']) && \str_contains($_SERVER['ES_VERSION'], 'SNAPSHOT')) {
    $_SERVER['ES_VERSION'] = \str_replace('-SNAPSHOT', '', $_SERVER['ES_VERSION']);
}

// Suppress specific deprecation notices for PHP 8.5 compatibility
if (\PHP_VERSION_ID >= 80500) {
    \set_error_handler(static function ($severity, $message, $file, $line) {
        // Suppress deprecation notices for __sleep() and __wakeup() magic methods
        if (\E_DEPRECATED === $severity
            && (\str_contains($message, '__sleep()') || \str_contains($message, '__wakeup()'))) {
            return true; // Suppress the error
        }

        // Let other errors be handled normally
        return false;
    }, \E_DEPRECATED);
}

require_once __DIR__.'/../vendor/autoload.php';
