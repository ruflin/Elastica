<?php

namespace Elastica\Test\Exception;

use Elastica\Exception\NotImplementedException;
use Elastica\Test\Base as BaseTest;

class NotImplementedTest extends BaseTest
{
    public function testInstance()
    {
        $code = 4;
        $message = 'Hello world';
        $exception = new NotImplementedException($message, $code);

        $this->assertInstanceOf('Elastica\Exception\NotImplementedException', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
        $this->assertInstanceOf('Exception', $exception);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
