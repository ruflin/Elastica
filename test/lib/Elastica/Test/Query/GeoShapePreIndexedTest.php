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
    public function testGeoProvided()
    {
        $index = $this->_createIndex();
        $indexName = $index->getName();
        $type = $index->getType('type');
        $otherType = $index->getType('other_type');

        // create mapping
        $mapping = new Mapping($type, [
            'location' => [
                'type' => 'geo_shape',
            ],
        ]);
        $type->setMapping($mapping);

        // create other type mapping
        $otherMapping = new Mapping($type, [
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
        $index->refresh();

        $gsp = new GeoShapePreIndexed(
            'location', '1', 'type', $indexName, 'location'
        );
        $gsp->setRelation(AbstractGeoShape::RELATION_INTERSECT);

        $expected = [
            'geo_shape' => [
                'location' => [
                    'indexed_shape' => [
                        'id' => '1',
                        'type' => 'type',
                        'index' => $indexName,
                        'path' => 'location',
                    ],
                    'relation' => $gsp->getRelation(),
                ],
            ],
        ];

        $this->assertEquals($expected, $gsp->toArray());

        $query = new BoolQuery();
        $query->addFilter($gsp);
        $results = $index->getType('type')->search($query);

        $this->assertEquals(1, $results->count());

        $index->delete();
    }

    /**
     * @group unit
     */
    public function testSetRelation()
    {
        $gsp = new GeoShapePreIndexed('location', '1', 'type', 'indexName', 'location');
        $gsp->setRelation(AbstractGeoShape::RELATION_INTERSECT);
        $this->assertEquals(AbstractGeoShape::RELATION_INTERSECT, $gsp->getRelation());
        $this->assertInstanceOf('Elastica\Query\GeoShapePreIndexed', $gsp->setRelation(AbstractGeoShape::RELATION_INTERSECT));
    }
}
