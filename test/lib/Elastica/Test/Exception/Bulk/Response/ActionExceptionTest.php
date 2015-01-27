<?php
namespace Elastica\Test\Exception\Bulk\Response;

use Elastica\Test\Base as BaseTest;

class ActionExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = $this->getMockBuilder('Elastica\Exception\Bulk\Response\ActionException')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
