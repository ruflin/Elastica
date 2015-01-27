<?php
namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Base as BaseTest;

class ThriftExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = $this->getMockBuilder('Elastica\Exception\Connection\ThriftException')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
