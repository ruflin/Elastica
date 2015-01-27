<?php
namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Base as BaseTest;

class ThriftExceptionTest extends BaseTest
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('Elasticsearch\\RestClient')) {
            self::markTestSkipped('munkie/elasticsearch-thrift-php package should be installed to run thrift exception tests');
        }
    }

    public function testInheritance()
    {
        $exception = $this->getMockBuilder('Elastica\Exception\Connection\ThriftException')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->assertInstanceOf('Exception', $exception);
        $this->assertInstanceOf('Elastica\Exception\ExceptionInterface', $exception);
    }
}
