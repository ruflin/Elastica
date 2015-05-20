<?php
namespace Elastica\Test\Exception\Connection;

use Elastica\Test\Exception\AbstractExceptionTest;

class ThriftExceptionTest extends AbstractExceptionTest
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('Elasticsearch\\RestClient')) {
            self::markTestSkipped('munkie/elasticsearch-thrift-php package should be installed to run thrift exception tests');
        }
    }
}
