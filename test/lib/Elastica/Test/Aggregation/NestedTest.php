<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Min;
use Elastica\Aggregation\Nested;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class NestedTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, [
            'resellers' => [
                'type' => 'nested',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'price' => ['type' => 'double'],
                ],
            ],
        ]));

        $type->addDocuments([
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

    /**
     * @group functional
     */
    public function testNestedAggregation()
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
}
