<?php

declare(strict_types=1);

// normalize ES_VERSION for version_compare
if (isset($_SERVER['ES_VERSION']) && \str_contains($_SERVER['ES_VERSION'], 'SNAPSHOT')) {
    $_SERVER['ES_VERSION'] = \str_replace('-SNAPSHOT', '', $_SERVER['ES_VERSION']);
}

require_once __DIR__.'/../vendor/autoload.php';

\set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (\E_USER_DEPRECATED === $severity && \str_contains($message, 'Function curl_close() is deprecated')) {
        return true;
    }

    return false;
});
