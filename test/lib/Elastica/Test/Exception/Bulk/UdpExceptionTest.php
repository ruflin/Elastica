<?php
namespace Elastica\Test\Exception\Bulk;

use Elastica\Exception\Bulk\UdpException;
use Elastica\Test\Base as BaseTest;

class UdpExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new UdpException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
