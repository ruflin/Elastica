<?php
namespace Elastica\Test\Exception;

use Elastica\Exception\NotFoundException;
use Elastica\Test\Base as BaseTest;

class NotFoundExceptionTest extends BaseTest
{
    public function testInheritance()
    {
        $exception = new NotFoundException();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
