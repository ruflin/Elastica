<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\InvalidException;
use Elastica\Test\Base as BaseTest;

class InvalidExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new InvalidException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
