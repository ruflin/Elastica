<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\NotImplementedException;
use Elastica\Test\Base as BaseTest;

class NotImplementedExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new NotImplementedException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }

    public function testInstance()
    {
        $code = 4;
        $message = 'Hello world';
        $exception = new NotImplementedException($message, $code);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
