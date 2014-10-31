<?php


namespace Elastica\Test\Filter;

use Elastica\Filter\AbstractGeoShape;
use Elastica\Filter\GeoShapeProvided;
use Elastica\Query\Filtered;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class GeoShapeProvidedTest extends BaseTest
{
    public function testConstructEnvelope()
    {
        $index = $this->_createIndex('geo_shape_filter_test');
        $type = $index->getType('test');

        // create mapping
        $mapping = new \Elastica\Type\Mapping($type, array(
            'location' => array(
                'type' => 'geo_shape'
            )
        ));
        $type->setMapping($mapping);

        // add docs
        $type->addDocument(new \Elastica\Document(1, array(
            'location' => array(
                "type"          => "envelope",
                "coordinates"   => array(
                    array(-50.0, 50.0),
                    array(50.0, -50.0)
                )
            )
        )));

        $index->optimize();
        $index->refresh();

        $envelope = array(
            array(25.0, 75.0),
            array(75.0, 25.0)
        );
        $gsp = new GeoShapeProvided('location', $envelope);

        $expected = array(
            'geo_shape' => array(
                'location' => array(
                    'shape' => array(
                        'type' => GeoShapeProvided::TYPE_ENVELOPE,
                        'coordinates' => $envelope
                    ),
                    'relation' => AbstractGeoShape::RELATION_INTERSECT
                ),
            )
        );

        $this->assertEquals($expected, $gsp->toArray());

        $query = new Filtered(new MatchAll(), $gsp);
        $results = $type->search($query);

        $this->assertEquals(1, $results->count());

        $index->delete();
    }

    public function testConstructPolygon()
    {
        $polygon = array(array(102.0, 2.0), array(103.0, 2.0), array(103.0, 3.0), array(103.0, 3.0), array(102.0, 2.0));
        $gsp = new GeoShapeProvided('location', $polygon, GeoShapeProvided::TYPE_POLYGON);

        $expected = array(
            'geo_shape' => array(
                'location' => array(
                    'shape' => array(
                        'type' => GeoShapeProvided::TYPE_POLYGON,
                        'coordinates' => $polygon
                    ),
                    'relation' => $gsp->getRelation()
                ),
            )
        );

        $this->assertEquals($expected, $gsp->toArray());
    }
}