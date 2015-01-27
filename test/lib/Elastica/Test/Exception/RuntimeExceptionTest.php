<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\RuntimeException;
use Elastica\Test\Base as BaseTest;

class RuntimeExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new RuntimeException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
