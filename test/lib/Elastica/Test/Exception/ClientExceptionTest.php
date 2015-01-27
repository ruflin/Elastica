<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\ClientException;
use Elastica\Test\Base as BaseTest;

class ClientExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new ClientException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
