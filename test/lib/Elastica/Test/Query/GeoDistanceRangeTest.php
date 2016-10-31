<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\GeoDistanceRange;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class GeoDistanceRangeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGeoPoint()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        // Set mapping
        $type->setMapping(['point' => ['type' => 'geo_point']]);

        // Add doc 1
        $doc1 = new Document(1,
            [
                'name' => 'ruflin',
            ]
        );

        $doc1->addGeoPoint('point', 17, 19);
        $type->addDocument($doc1);

        // Add doc 2
        $doc2 = new Document(2,
            [
                'name' => 'ruflin',
            ]
        );

        $doc2->addGeoPoint('point', 30, 40);
        $type->addDocument($doc2);

        $index->refresh();
        // Set geo distance sorting with coordinates near to first doc.
        $geoSort =
            ['_geo_distance' => [
                'point' => [
                    'lat' => 15,
                    'lon' => 20,
                ],
                'order' => 'asc',
                'unit' => 'km',
                'distance_type' => 'plane',
            ]];

        $query = new Query(new MatchAll());
        $query->setSort($geoSort);
        //doc #1 are the nearest to sorting point and must be first in result
        $this->assertEquals(1, $type->search($query)->current()->getId());
        $this->assertEquals(2, $type->search($query)->next()->getId());
    }

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
