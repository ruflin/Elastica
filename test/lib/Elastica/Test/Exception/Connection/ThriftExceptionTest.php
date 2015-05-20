<?php
namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Exception\AbstractExceptionTest;

class ThriftExceptionTest extends AbstractExceptionTest
{
    public static function setUpBeforeClass()
    {
        self::markTestSkipped('pecl/memcache must be installed to run this test case');

        if (!class_exists('Elasticsearch\\RestClient')) {
            self::markTestSkipped('munkie/elasticsearch-thrift-php package should be installed to run thrift exception tests');
        }
    }
}
