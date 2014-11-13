<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\GeoDistanceRange;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class GeoDistanceRangeTest extends BaseTest
{
    public function testGeoPoint()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);

        $type = $index->getType('test');

        // Set mapping
        $type->setMapping(array('point' => array('type' => 'geo_point')));

        // Add doc 1
        $doc1 = new Document(1,
            array(
                'name' => 'ruflin',
            )
        );

        $doc1->addGeoPoint('point', 17, 19);
        $type->addDocument($doc1);

        // Add doc 2
        $doc2 = new Document(2,
            array(
                'name' => 'ruflin',
            )
        );

        $doc2->addGeoPoint('point', 30, 40);
        $type->addDocument($doc2);

        $index->optimize();
        $index->refresh();

        // Only one point should be in radius
        $query = new Query();
        $geoFilter = new GeoDistanceRange(
            'point',
            array('lat' => 30, 'lon' => 40),
            array('from' => '0km', 'to' => '2km')
        );

        $query = new Query(new MatchAll());
        $query->setPostFilter($geoFilter);
        $this->assertEquals(1, $type->search($query)->count());

        // Both points should be inside
        $query = new Query();
        $geoFilter = new GeoDistanceRange(
            'point',
            array('lat' => 30, 'lon' => 40),
            array('gte' => '0km', 'lte' => '40000km')
        );
        $query = new Query(new MatchAll());
        $query->setPostFilter($geoFilter);
        $index->refresh();

        $this->assertEquals(2, $type->search($query)->count());
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidRange()
    {
        $geoFilter = new GeoDistanceRange(
            'point',
            array('lat' => 30, 'lon' => 40),
            array('invalid' => '0km', 'lte' => '40000km')
        );
    }

    /**
     * @dataProvider invalidLocationDataProvider
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidLocation($location)
    {
        $geoFilter = new GeoDistanceRange(
            'point',
            $location,
            array('gt' => '0km', 'lte' => '40000km')
        );
    }

    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct($key, $location, $ranges, $expected)
    {
        $filter = new GeoDistanceRange($key, $location, $ranges);

        $data = $filter->toArray();

        $this->assertEquals($expected, $data);
    }

    public function invalidLocationDataProvider()
    {
        return array(
            array(
                array('lat' => 1.0),
            ),
            array(
                array('lon' => 1.0),
            ),
            array(
                array(),
            ),
            array(
                new \stdClass(),
            ),
            array(
                null,
            ),
            array(
                true,
            ),
            array(
                false,
            )
        );
    }

    public function constructDataProvider()
    {
        return array(
            array(
                'location',
                'u09tvqx',
                array(
                    'from' => '10km',
                    'to' => '20km',
                ),
                array(
                    'geo_distance_range' => array(
                        'from' => '10km',
                        'to' => '20km',
                        'location' => 'u09tvqx',
                    )
                )
            ),
            array(
                'location',
                'u09tvqx',
                array(
                    'to' => '20km',
                    'include_upper' => 0,
                    'from' => '10km',
                    'include_lower' => 1,
                ),
                array(
                    'geo_distance_range' => array(
                        'to' => '20km',
                        'include_upper' => false,
                        'from' => '10km',
                        'include_lower' => true,
                        'location' => 'u09tvqx',
                    )
                )
            ),
            array(
                'location',
                array(
                    'lon' => 2.35,
                    'lat' => 48.86,
                ),
                array(
                    'lte' => '20km',
                    'gt' => '10km',
                ),
                array(
                    'geo_distance_range' => array(
                        'lte' => '20km',
                        'gt' => '10km',
                        'location' => array(
                            'lat' => 48.86,
                            'lon' => 2.35,
                        ),
                    )
                )
            ),
            array(
                'location',
                array(
                    'lat' => 48.86,
                    'lon' => 2.35,
                ),
                array(
                    'lt' => '20km',
                    'gte' => '10km',
                ),
                array(
                    'geo_distance_range' => array(
                        'lt' => '20km',
                        'gte' => '10km',
                        'location' => array(
                            'lat' => 48.86,
                            'lon' => 2.35,
                        ),
                    )
                )
            )
        );
    }
}
