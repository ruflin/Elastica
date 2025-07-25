<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Exception\InvalidException;
use Elastica\Query\GeoBoundingBox;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class GeoBoundingBoxTest extends BaseTest
{
    #[Group('unit')]
    public function testAddCoordinates(): void
    {
        $key = 'pin.location';
        $coords = ['40.73, -74.1', '40.01, -71.12'];
        $query = new GeoBoundingBox($key, ['1,2', '3,4']);

        $query->addCoordinates($key, $coords);
        $expectedArray = ['top_left' => $coords[0], 'bottom_right' => $coords[1]];
        $this->assertSame($expectedArray, $query->getParam($key));
    }

    #[Group('unit')]
    public function testAddCoordinatesInvalidException(): void
    {
        $this->expectException(InvalidException::class);

        new GeoBoundingBox('foo', []);
    }

    #[Group('unit')]
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
