<?php
namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Base as BaseTest;

class GuzzleExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = $this->getMockBuilder('Elastica\Exception\Connection\GuzzleException')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
