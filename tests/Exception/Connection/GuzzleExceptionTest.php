<?php

namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Exception\AbstractExceptionTest;

/**
 * @internal
 */
class GuzzleExceptionTest extends AbstractExceptionTest
{
    public static function setUpbeforeClass(): void
    {
        if (!\class_exists('GuzzleHttp\\Client')) {
            self::markTestSkipped('guzzlehttp/guzzle package should be installed to run guzzle transport tests');
        }
    }
}
