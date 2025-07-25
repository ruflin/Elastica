<?php

declare(strict_types=1);

namespace Elastica\Test\Exception;

use Elastica\Exception\NotImplementedException;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class NotImplementedExceptionTest extends AbstractExceptionTestCase
{
    #[Group('unit')]
    public function testInstance(): void
    {
        $code = 4;
        $message = 'Hello world';
        $exception = new NotImplementedException($message, $code);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
