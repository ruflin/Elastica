<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\JSONParseException;
use Elastica\Test\Base as BaseTest;

class JSONParseExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new JSONParseException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
