<?php
namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\AbstractGeoShape;
use Elastica\Filter\GeoShapeProvided;
use Elastica\Query\BoolQuery;
use Elastica\Test\DeprecatedClassBase as BaseTest;
use Elastica\Type\Mapping;

class GeoShapeProvidedTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new GeoShapeProvided('location', array()));
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group functional
     */
    public function testConstructEnvelope()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        // create mapping
        $mapping = new Mapping($type, array(
            'location' => array(
                'type' => 'geo_shape',
            ),
        ));
        $type->setMapping($mapping);

        // add docs
        $type->addDocument(new Document(1, array(
            'location' => array(
                'type' => 'envelope',
                'coordinates' => array(
                    array(-50.0, 50.0),
                    array(50.0, -50.0),
                ),
            ),
        )));

        $index->optimize();
        $index->refresh();

        $envelope = array(
            array(25.0, 75.0),
            array(75.0, 25.0),
        );
        $gsp = new GeoShapeProvided('location', $envelope);

        $expected = array(
            'geo_shape' => array(
                'location' => array(
                    'shape' => array(
                        'type' => GeoShapeProvided::TYPE_ENVELOPE,
                        'coordinates' => $envelope,
                        'relation' => AbstractGeoShape::RELATION_INTERSECT,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $gsp->toArray());

        $query = new BoolQuery();
        $query->addFilter($gsp);
        $results = $type->search($query);

        $this->assertEquals(1, $results->count());
    }

    /**
     * @group unit
     */
    public function testConstructPolygon()
    {
        $polygon = array(array(102.0, 2.0), array(103.0, 2.0), array(103.0, 3.0), array(103.0, 3.0), array(102.0, 2.0));
        $gsp = new GeoShapeProvided('location', $polygon, GeoShapeProvided::TYPE_POLYGON);

        $expected = array(
            'geo_shape' => array(
                'location' => array(
                    'shape' => array(
                        'type' => GeoShapeProvided::TYPE_POLYGON,
                        'coordinates' => $polygon,
                        'relation' => $gsp->getRelation(),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $gsp->toArray());
    }

    /**
     * @group unit
     */
    public function testSetRelation()
    {
        $gsp = new GeoShapeProvided('location', array(array(25.0, 75.0), array(75.0, 25.0)));
        $gsp->setRelation(AbstractGeoShape::RELATION_INTERSECT);
        $this->assertEquals(AbstractGeoShape::RELATION_INTERSECT, $gsp->getRelation());
        $this->assertInstanceOf('Elastica\Filter\GeoShapeProvided', $gsp->setRelation(AbstractGeoShape::RELATION_INTERSECT));
    }
}
