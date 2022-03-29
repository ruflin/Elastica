<?php

namespace Elastica\Test\Exception;

use Elastica\Exception\RequestEntityTooLargeException;

/**
 * @internal
 */
class RequestEntityTooLargeExceptionTest extends AbstractExceptionTest
{
    /**
     * @group unit
     */
    public function testInstanceDefaultMessage(): void
    {
        $message = 'Request entity is too large.';
        $exception = new RequestEntityTooLargeException();
        $this->assertEquals($message, $exception->getMessage());
    }
}
