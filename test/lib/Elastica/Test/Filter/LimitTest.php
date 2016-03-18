<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Limit;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class LimitTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new Limit(10));
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
    public function testSetType()
    {
        $filter = new Limit(10);
        $this->assertEquals(10, $filter->getParam('value'));

        $this->assertInstanceOf('Elastica\Filter\Limit', $filter->setLimit(20));
        $this->assertEquals(20, $filter->getParam('value'));
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $filter = new Limit(15);

        $expectedArray = array(
            'limit' => array('value' => 15),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
