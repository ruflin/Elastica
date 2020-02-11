<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Cardinality;
use Elastica\Query;

class AggregationMetadataTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testAggregationSimpleMetadata()
    {
        $aggName = 'mock';
        $metadata = ['color' => 'blue'];

        $agg = new Cardinality($aggName);
        $agg->setField('mock_field');
        $agg->setMeta($metadata);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation($aggName);

        $this->assertEquals($metadata, $results['meta']);
    }

    /**
     * @group functional
     */
    public function testAggregationComplexMetadata()
    {
        $aggName = 'mock';
        $metadata = [
            'color' => 'blue',
            'status' => 'green',
            'users' => [
                'foo' => 'bar',
                'moo' => 'baz',
            ],
        ];

        $agg = new Cardinality($aggName);
        $agg->setField('mock_field');
        $agg->setMeta($metadata);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation($aggName);

        $this->assertEquals($metadata, $results['meta']);
    }
}
