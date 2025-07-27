<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GeoDistance;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class GeoDistanceTest extends BaseAggregationTestCase
{
    #[Group('functional')]
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

    #[Group('functional')]
    public function testGeoDistanceKeyedAggregation(): void
    {
        $agg = new GeoDistance('geo', 'location', ['lat' => 32.804654, 'lon' => -117.242594]);
        $agg->addRange(null, 100);
        $agg->setKeyed();
        $agg->setUnit('mi');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('geo');

        $expected = [
            '*-100.0',
        ];
        $this->assertSame($expected, \array_keys($results['buckets']));
    }

    /**
     * @group unit
     */
    public function testGeoDistanceAggregationWithKey(): void
    {
        $agg = new GeoDistance('geo', 'location', ['lat' => 32.804654, 'lon' => -117.242594]);
        $agg->addRange(null, 10, 'first');
        $agg->addRange(10, null, 'second');
        $agg->setUnit('mi');

        $expected = [
            'geo_distance' => [
                'field' => 'location',
                'origin' => ['lat' => 32.804654, 'lon' => -117.242594],
                'unit' => 'mi',
                'ranges' => [
                    [
                        'to' => 10,
                        'key' => 'first',
                    ],
                    [
                        'from' => 10,
                        'key' => 'second',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());
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
