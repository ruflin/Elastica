<?php

namespace Elastica\Test\Filter;

use Elastica\Test\DeprecatedClassBase as BaseTest;

class AbstractGeoDistanceTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass('Elastica\Filter\AbstractGeoDistance');
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }
}
