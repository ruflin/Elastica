<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Cardinality;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class AggregationMetadataTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testAggregationSimpleMetadata(): void
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

    #[Group('functional')]
    public function testAggregationComplexMetadata(): void
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

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->refresh();

        return $index;
    }
}
