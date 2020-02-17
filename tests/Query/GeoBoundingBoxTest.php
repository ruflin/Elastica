<?php

namespace Elastica\Test\Query;

use Elastica\Query\GeoBoundingBox;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class GeoBoundingBoxTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testAddCoordinates(): void
    {
        $key = 'pin.location';
        $coords = ['40.73, -74.1', '40.01, -71.12'];
        $query = new GeoBoundingBox($key, ['1,2', '3,4']);

        $query->addCoordinates($key, $coords);
        $expectedArray = ['top_left' => $coords[0], 'bottom_right' => $coords[1]];
        $this->assertEquals($expectedArray, $query->getParam($key));

        $returnValue = $query->addCoordinates($key, $coords);
        $this->assertInstanceOf(GeoBoundingBox::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testAddCoordinatesInvalidException(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $query = new GeoBoundingBox('foo', []);
    }

    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $key = 'pin.location';
        $coords = ['40.73, -74.1', '40.01, -71.12'];
        $query = new GeoBoundingBox($key, $coords);

        $expectedArray = [
            'geo_bounding_box' => [
                $key => [
                    'top_left' => $coords[0],
                    'bottom_right' => $coords[1],
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
