<?php
namespace Elastica\Test\Exception;

use Elastica\Test\Base as BaseTest;

class ConnectionExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = $this->getMockBuilder('Elastica\Exception\ConnectionException')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
