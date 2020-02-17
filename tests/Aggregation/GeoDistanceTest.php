<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GeoDistance;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class GeoDistanceTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testGeoDistanceAggregation(): void
    {
        $agg = new GeoDistance('geo', 'location', ['lat' => 32.804654, 'lon' => -117.242594]);
        $agg->addRange(null, 100);
        $agg->setUnit('mi');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('geo');

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'location' => ['type' => 'geo_point'],
        ]));

        $index->addDocuments([
            new Document(1, ['location' => ['lat' => 32.849437, 'lon' => -117.271732]]),
            new Document(2, ['location' => ['lat' => 32.798320, 'lon' => -117.246648]]),
            new Document(3, ['location' => ['lat' => 37.782439, 'lon' => -122.392560]]),
        ]);

        $index->refresh();

        return $index;
    }
}
