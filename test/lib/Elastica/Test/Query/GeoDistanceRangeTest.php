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

        $index->optimize();
        $index->refresh();

        // Only one point should be in radius
        $query = new Query();
        $geoQuery = new GeoDistanceRange(
            'point',
            ['lat' => 30, 'lon' => 40],
            ['from' => '0km', 'to' => '2km']
        );

        $query = new Query(new MatchAll());
        $query->setPostFilter($geoQuery);
        $this->assertEquals(1, $type->search($query)->count());

        // Both points should be inside
        $query = new Query();
        $geoQuery = new GeoDistanceRange(
            'point',
            ['lat' => 30, 'lon' => 40],
            ['gte' => '0km', 'lte' => '4000km']
        );
        $query = new Query(new MatchAll());
        $query->setPostFilter($geoQuery);
        $index->refresh();

        $this->assertEquals(2, $type->search($query)->count());
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
