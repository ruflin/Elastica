<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query\AbstractGeoShape;
use Elastica\Query\BoolQuery;
use Elastica\Query\GeoShapeProvided;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class GeoShapeProvidedTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch(): void
    {
        $index = $this->_createIndex();
        $mapping = new Mapping([
            'location' => [
                'type' => 'geo_shape',
            ],
        ]);
        $index->setMapping($mapping);

        // add docs
        $index->addDocument(new Document(1, [
            'location' => [
                'type' => 'envelope',
                'coordinates' => [
                    [-50.0, 50.0],
                    [50.0, -50.0],
                ],
            ],
        ]));

        $index->forcemerge();
        $index->refresh();

        $envelope = [
            [25.0, 75.0],
            [75.0, 25.0],
        ];
        $gsp = new GeoShapeProvided('location', $envelope);

        $query = new BoolQuery();
        $query->addFilter($gsp);

        $this->assertEquals(1, $index->count($query));

        $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT);
        $this->assertEquals(0, $index->count($query), 'Changing the relation should take effect');

        $index->delete();
    }

    /**
     * @group unit
     */
    public function testConstructEnvelope(): void
    {
        $envelope = [
            [25.0, 75.0],
            [75.0, 25.0],
        ];
        $gsp = new GeoShapeProvided('location', $envelope);

        $expected = [
            'geo_shape' => [
                'location' => [
                    'shape' => [
                        'type' => GeoShapeProvided::TYPE_ENVELOPE,
                        'coordinates' => $envelope,
                    ],
                    'relation' => $gsp->getRelation(),
                ],
            ],
        ];

        $this->assertEquals($expected, $gsp->toArray());
    }

    /**
     * @group unit
     */
    public function testConstructPolygon(): void
    {
        $polygon = [[102.0, 2.0], [103.0, 2.0], [103.0, 3.0], [103.0, 3.0], [102.0, 2.0]];
        $gsp = new GeoShapeProvided('location', $polygon, GeoShapeProvided::TYPE_POLYGON);

        $expected = [
            'geo_shape' => [
                'location' => [
                    'shape' => [
                        'type' => GeoShapeProvided::TYPE_POLYGON,
                        'coordinates' => $polygon,
                    ],
                    'relation' => $gsp->getRelation(),
                ],
            ],
        ];

        $this->assertEquals($expected, $gsp->toArray());
    }

    /**
     * @group unit
     */
    public function testSetRelation(): void
    {
        $gsp = new GeoShapeProvided('location', [[25.0, 75.0], [75.0, 25.0]]);

        $this->assertEquals(AbstractGeoShape::RELATION_INTERSECT, $gsp->getRelation());
        $this->assertSame($gsp, $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT));
        $this->assertEquals(AbstractGeoShape::RELATION_DISJOINT, $gsp->getRelation());
    }
}
