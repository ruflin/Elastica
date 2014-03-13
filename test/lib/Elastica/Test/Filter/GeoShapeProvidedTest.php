<?php


namespace Elastica\Test\Filter;

use Elastica\Filter\GeoShapeProvided;
use Elastica\Test\Base as BaseTest;

class GeoShapePreIndexedTest extends BaseTest
{
    public function testConstructEnvelope()
    {
        $envelope = array(
            array(13.0, 53.0),
            array(14.0, 52.0)
        );
        $gsp = new GeoShapeProvided('location', $envelope);

        $expected = array(
            'geo_shape' => array(
                'location' => array(
                    'shape' => array(
                        'type' => GeoShapeProvided::TYPE_ENVELOPE,
                        'coordinates' => $envelope
                    ),
                    'relation' => $gsp->getRelation()
                ),
            )
        );

        $this->assertEquals($expected, $gsp->toArray());
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