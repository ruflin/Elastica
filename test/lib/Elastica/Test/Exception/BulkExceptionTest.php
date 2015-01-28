<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\BulkException;
use Elastica\Test\Base as BaseTest;

class BulkExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new BulkException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
