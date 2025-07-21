<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GeotileGridAggregation;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class GeotileGridAggregationTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testGeotileGridAggregation(): void
    {
        $agg = new GeotileGridAggregation('tile', 'location');
        $agg->setPrecision(7);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('tile');

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
        $this->assertEquals(1, $results['buckets'][1]['doc_count']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'location' => ['type' => 'geo_point'],
        ]));

        $index->addDocuments([
            new Document('1', ['location' => ['lat' => 32.849437, 'lon' => -117.271732]]),
            new Document('2', ['location' => ['lat' => 32.798320, 'lon' => -117.246648]]),
            new Document('3', ['location' => ['lat' => 37.782439, 'lon' => -122.392560]]),
        ]);

        $index->refresh();

        return $index;
    }
}
