<?php
namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Base as BaseTest;

class HttpExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = $this->getMockBuilder('Elastica\Exception\Connection\HttpException')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
