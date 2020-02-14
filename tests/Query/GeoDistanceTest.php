<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\GeoDistance;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class GeoDistanceTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGeoPoint(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping(['point' => ['type' => 'geo_point']]));

        // Add doc 1
        $doc1 = new Document(1);
        $doc1->addGeoPoint('point', 17, 19);
        $index->addDocument($doc1);

        // Add doc 2
        $doc2 = new Document(2);
        $doc2->addGeoPoint('point', 30, 40);
        $index->addDocument($doc2);

        $index->forcemerge();
        $index->refresh();

        // Only one point should be in radius
        $geoQuery = new GeoDistance('point', ['lat' => 30, 'lon' => 40], '1km');
        $query = new Query();
        $query->setPostFilter($geoQuery);

        $this->assertEquals(1, $index->count($query));

        // Both points should be inside
        $geoQuery = new GeoDistance('point', ['lat' => 30, 'lon' => 40], '40000km');
        $query = new Query();
        $query->setPostFilter($geoQuery);

        $this->assertEquals(2, $index->count($query));
    }

    /**
     * @group unit
     */
    public function testConstructLatlon(): void
    {
        $key = 'location';
        $location = [
            'lat' => 48.86,
            'lon' => 2.35,
        ];
        $distance = '10km';

        $query = new GeoDistance($key, $location, $distance);

        $expected = [
            'geo_distance' => [
                $key => $location,
                'distance' => $distance,
            ],
        ];

        $data = $query->toArray();

        $this->assertEquals($expected, $data);
    }

    /**
     * @group unit
     */
    public function testConstructGeohash(): void
    {
        $key = 'location';
        $location = 'u09tvqx';
        $distance = '10km';

        $query = new GeoDistance($key, $location, $distance);

        $expected = [
            'geo_distance' => [
                $key => $location,
                'distance' => $distance,
            ],
        ];

        $data = $query->toArray();

        $this->assertEquals($expected, $data);
    }

    /**
     * @group unit
     */
    public function testSetDistanceType(): void
    {
        $query = new GeoDistance('location', ['lat' => 48.86, 'lon' => 2.35], '10km');
        $distanceType = GeoDistance::DISTANCE_TYPE_ARC;
        $query->setDistanceType($distanceType);

        $data = $query->toArray();

        $this->assertEquals($distanceType, $data['geo_distance']['distance_type']);
    }
}
