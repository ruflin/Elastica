<?php


namespace Elastica\Test\Filter;

use Elastica\Filter\GeoShapePreIndexed;
use Elastica\Test\Base as BaseTest;

class GeoShapePreIndexedTest extends BaseTest
{
    public function testGeoProvided()
    {
        $gsp = new GeoShapePreIndexed(
            'location', 1, 'indexed_location', 'shapes', 'shape'
        );

        $expected = array(
            'geo_shape' => array(
                'location' => array(
                    'indexed_shape' => array(
                        'id' => 1,
                        'type' => 'indexed_location',
                        'index' => 'shapes',
                        'path' => 'shape'
                    ),
                    'relation' => $gsp->getRelation()
                )
            )
        );

        $this->assertEquals($expected, $gsp->toArray());
    }
}