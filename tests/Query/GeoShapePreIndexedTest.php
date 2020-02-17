<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query\AbstractGeoShape;
use Elastica\Query\BoolQuery;
use Elastica\Query\GeoShapePreIndexed;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class GeoShapePreIndexedTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch(): void
    {
        $index = $this->_createIndex();
        $indexName = $index->getName();
        $mapping = new Mapping([
            'location' => [
                'type' => 'geo_shape',
            ],
        ]);
        $index->setMapping($mapping);

        // add other type docs
        $index->addDocument(new Document('2', [
            'location' => [
                'type' => 'envelope',
                'coordinates' => [
                    [25.0, 75.0],
                    [75.0, 25.0],
                ],
            ],
        ]));

        $index->forcemerge();
        $index->refresh();

        $gsp = new GeoShapePreIndexed(
            'location',
            '2',
            $indexName,
            'location'
        );

        $query = new BoolQuery();
        $query->addFilter($gsp);

        $this->assertEquals(1, $index->count($query));

        $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT);
        $this->assertEquals(0, $index->count($query), 'Changing the relation should take effect');
    }

    /**
     * @group unit
     */
    public function testConstruct(): void
    {
        $gsp = new GeoShapePreIndexed(
            'search_field',
            '1',
            'index',
            'indexed_field'
        );

        $expected = [
            'geo_shape' => [
                'search_field' => [
                    'indexed_shape' => [
                        'id' => '1',
                        'index' => 'index',
                        'path' => 'indexed_field',
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
        $gsp = new GeoShapePreIndexed('location', '1', 'indexName', 'location');

        $this->assertEquals(AbstractGeoShape::RELATION_INTERSECT, $gsp->getRelation());
        $this->assertSame($gsp, $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT));
        $this->assertEquals(AbstractGeoShape::RELATION_DISJOINT, $gsp->getRelation());
    }
}
