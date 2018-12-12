<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\AbstractGeoShape;
use Elastica\Query\BoolQuery;
use Elastica\Query\GeoShapeProvided;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class GeoShapeProvidedTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        // create mapping
        $mapping = new Mapping($type, [
            'location' => [
                'type' => 'geo_shape',
            ],
        ]);
        $type->setMapping($mapping);

        // add docs
        $type->addDocument(new Document(1, [
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

        $this->assertEquals(1, $type->count($query));

        $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT);
        $this->assertEquals(0, $type->count($query), 'Changing the relation should take effect');

        $index->delete();
    }

    /**
     * @group unit
     */
    public function testConstructEnvelope()
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
    public function testConstructPolygon()
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
    public function testSetRelation()
    {
        $gsp = new GeoShapeProvided('location', [[25.0, 75.0], [75.0, 25.0]]);

        $this->assertEquals(AbstractGeoShape::RELATION_INTERSECT, $gsp->getRelation());
        $this->assertSame($gsp, $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT));
        $this->assertEquals(AbstractGeoShape::RELATION_DISJOINT, $gsp->getRelation());
    }
}
