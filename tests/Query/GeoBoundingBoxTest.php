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
    public function testNamedCoordinates(): void
    {
        foreach ([
            ['top_right' => ['lat' => 40.73, 'lon' => -71.12], 'bottom_left' => ['lat' => 40.01, 'lon' => -74.1]],
            ['top_left' => '40.73, -74.1', 'bottom_right' => '40.01, -71.12'],
            ['top' => 40.73, 'left' => -74.1, 'bottom' => 0, 'right' => 0],
            ['wkt' => 'BBOX (-74.1, -71.12, 40.73, 40.01)'],
        ] as $coordinates) {
            $query = new GeoBoundingBox('pin.location', $coordinates);
            $this->assertSame(['geo_bounding_box' => ['pin.location' => $coordinates]], $query->toArray());

            $query = new GeoBoundingBox('pin.location', ['1,2', '3,4']);
            $this->assertSame($query, $query->addCoordinates('pin.location', $coordinates));
            $this->assertSame($coordinates, $query->getParam('pin.location'));
        }
    }

    #[Group('unit')]
    public function testSetRawCoordinates(): void
    {
        $query = new GeoBoundingBox('pin.location', ['1,2', '3,4']);
        $coordinates = ['top_right' => [0, 40.73], 'bottom_left' => [-74.1, 0]];

        $this->assertSame($query, $query->setRawCoordinates('pin.location', $coordinates));
        $this->assertSame(['geo_bounding_box' => ['pin.location' => $coordinates]], $query->toArray());
    }

    #[Group('unit')]
    public function testIncompleteCoordinatesAreRejected(): void
    {
        $this->expectException(InvalidException::class);

        new GeoBoundingBox('pin.location', ['top_right' => [0, 0]]);
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
