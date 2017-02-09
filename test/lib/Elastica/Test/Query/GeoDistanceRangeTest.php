<?php
namespace Elastica\Test\Query;

use Elastica\Query\GeoDistanceRange;
use Elastica\Test\DeprecatedClassBase;

class GeoDistanceRangeTest extends DeprecatedClassBase
{
    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidRange()
    {
        $geoQuery = new GeoDistanceRange(
            'point',
            ['lat' => 30, 'lon' => 40],
            ['invalid' => '0km', 'lte' => '40000km']
        );
    }

    /**
     * @group unit
     * @dataProvider invalidLocationDataProvider
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidLocation($location)
    {
        $geoQuery = new GeoDistanceRange(
            'point',
            $location,
            ['gt' => '0km', 'lte' => '40000km']
        );
    }

    /**
     * @group unit
     * @dataProvider constructDataProvider
     */
    public function testConstruct($key, $location, $ranges, $expected)
    {
        $query = new GeoDistanceRange($key, $location, $ranges);

        $data = $query->toArray();

        $this->assertEquals($expected, $data);
    }

    public function invalidLocationDataProvider()
    {
        return [
            [
                ['lat' => 1.0],
            ],
            [
                ['lon' => 1.0],
            ],
            [
                [],
            ],
            [
                new \stdClass(),
            ],
            [
                null,
            ],
            [
                true,
            ],
            [
                false,
            ],
        ];
    }

    public function constructDataProvider()
    {
        return [
            [
                'location',
                'u09tvqx',
                [
                    'from' => '10km',
                    'to' => '20km',
                ],
                [
                    'geo_distance_range' => [
                        'from' => '10km',
                        'to' => '20km',
                        'location' => 'u09tvqx',
                    ],
                ],
            ],
            [
                'location',
                'u09tvqx',
                [
                    'to' => '20km',
                    'include_upper' => 0,
                    'from' => '10km',
                    'include_lower' => 1,
                ],
                [
                    'geo_distance_range' => [
                        'to' => '20km',
                        'include_upper' => false,
                        'from' => '10km',
                        'include_lower' => true,
                        'location' => 'u09tvqx',
                    ],
                ],
            ],
            [
                'location',
                [
                    'lon' => 2.35,
                    'lat' => 48.86,
                ],
                [
                    'lte' => '20km',
                    'gt' => '10km',
                ],
                [
                    'geo_distance_range' => [
                        'lte' => '20km',
                        'gt' => '10km',
                        'location' => [
                            'lat' => 48.86,
                            'lon' => 2.35,
                        ],
                    ],
                ],
            ],
            [
                'location',
                [
                    'lat' => 48.86,
                    'lon' => 2.35,
                ],
                [
                    'lt' => '20km',
                    'gte' => '10km',
                ],
                [
                    'geo_distance_range' => [
                        'lt' => '20km',
                        'gte' => '10km',
                        'location' => [
                            'lat' => 48.86,
                            'lon' => 2.35,
                        ],
                    ],
                ],
            ],
        ];
    }
}
