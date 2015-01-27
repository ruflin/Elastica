<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\QueryBuilderException;
use Elastica\Test\Base as BaseTest;

class QueryBuilderExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new QueryBuilderException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
