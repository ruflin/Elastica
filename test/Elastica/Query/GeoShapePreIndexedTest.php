<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\AbstractGeoShape;
use Elastica\Query\BoolQuery;
use Elastica\Query\GeoShapePreIndexed;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class GeoShapePreIndexedTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();
        $index2 = $this->_createIndex();
        $indexName = $index->getName();
        $indexName2 = $index2->getName();
        $type = $index->getType('type');
        $otherType = $index2->getType('other_type');

        // create mapping
        $mapping = new Mapping($type, [
            'location' => [
                'type' => 'geo_shape',
            ],
        ]);
        $type->setMapping($mapping);

        // create other type mapping
        $otherMapping = new Mapping($otherType, [
            'location' => [
                'type' => 'geo_shape',
            ],
        ]);
        $otherType->setMapping($otherMapping);

        // add type docs
        $type->addDocument(new Document('1', [
            'location' => [
                'type' => 'envelope',
                'coordinates' => [
                    [0.0, 50.0],
                    [50.0, 0.0],
                ],
            ],
        ]));

        // add other type docs
        $otherType->addDocument(new Document('2', [
            'location' => [
                'type' => 'envelope',
                'coordinates' => [
                    [25.0, 75.0],
                    [75.0, 25.0],
                ],
            ],
        ]));

        $index->forcemerge();
        $index2->forcemerge();
        $index->refresh();
        $index2->refresh();

        $gsp = new GeoShapePreIndexed(
            'location', '2', 'other_type', $indexName2, 'location'
        );

        $query = new BoolQuery();
        $query->addFilter($gsp);

        $this->assertEquals(1, $otherType->count($query));

        $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT);
        $this->assertEquals(0, $otherType->count($query), 'Changing the relation should take effect');

        $index->delete();
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $gsp = new GeoShapePreIndexed(
            'search_field', '1', 'type', 'index', 'indexed_field'
        );

        $expected = [
            'geo_shape' => [
                'search_field' => [
                    'indexed_shape' => [
                        'id' => '1',
                        'type' => 'type',
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
    public function testSetRelation()
    {
        $gsp = new GeoShapePreIndexed('location', '1', 'type', 'indexName', 'location');

        $this->assertEquals(AbstractGeoShape::RELATION_INTERSECT, $gsp->getRelation());
        $this->assertSame($gsp, $gsp->setRelation(AbstractGeoShape::RELATION_DISJOINT));
        $this->assertEquals(AbstractGeoShape::RELATION_DISJOINT, $gsp->getRelation());
    }
}
