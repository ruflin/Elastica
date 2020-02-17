<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Min;
use Elastica\Aggregation\Nested;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class NestedTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testNestedAggregation(): void
    {
        $agg = new Nested('resellers', 'resellers');
        $min = new Min('min_price');
        $min->setField('resellers.price');
        $agg->addAggregation($min);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('resellers');

        $this->assertEquals(4.98, $results['min_price']['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->setMapping(new Mapping([
            'resellers' => [
                'type' => 'nested',
                'properties' => [
                    'name' => ['type' => 'text'],
                    'price' => ['type' => 'double'],
                ],
            ],
        ]));

        $index->addDocuments([
            new Document(1, [
                'resellers' => [
                    'name' => 'spacely sprockets',
                    'price' => 5.55,
                ],
            ]),
            new Document(2, [
                'resellers' => [
                    'name' => 'cogswell cogs',
                    'price' => 4.98,
                ],
            ]),
        ]);

        $index->refresh();

        return $index;
    }
}
