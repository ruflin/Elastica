<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\GeoDistance;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class GeoDistanceTest extends BaseTest
{
    public function testGeoPoint()
    {
        $index = $this->_createIndex();

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
        $geoFilter = new GeoDistance('point', array('lat' => 30, 'lon' => 40), '1km');

        $query = new Query(new MatchAll());
        $query->setPostFilter($geoFilter);
        $this->assertEquals(1, $type->search($query)->count());

        // Both points should be inside
        $query = new Query();
        $geoFilter = new GeoDistance('point', array('lat' => 30, 'lon' => 40), '40000km');
        $query = new Query(new MatchAll());
        $query->setPostFilter($geoFilter);
        $index->refresh();

        $this->assertEquals(2, $type->search($query)->count());
    }

    public function testConstructLatlon()
    {
        $key = 'location';
        $location = array(
            'lat' => 48.86,
            'lon' => 2.35,
        );
        $distance = '10km';

        $filter = new GeoDistance($key, $location, $distance);

        $expected = array(
            'geo_distance' => array(
                $key => $location,
                'distance' => $distance,
            ),
        );

        $data = $filter->toArray();

        $this->assertEquals($expected, $data);
    }

    public function testConstructGeohash()
    {
        $key = 'location';
        $location = 'u09tvqx';
        $distance = '10km';

        $filter = new GeoDistance($key, $location, $distance);

        $expected = array(
            'geo_distance' => array(
                $key => $location,
                'distance' => $distance,
            ),
        );

        $data = $filter->toArray();

        $this->assertEquals($expected, $data);
    }

    public function testSetDistanceType()
    {
        $filter = new GeoDistance('location', array('lat' => 48.86, 'lon' => 2.35), '10km');
        $distanceType = GeoDistance::DISTANCE_TYPE_ARC;
        $filter->setDistanceType($distanceType);

        $data = $filter->toArray();

        $this->assertEquals($distanceType, $data['geo_distance']['distance_type']);
    }

    public function testSetOptimizeBbox()
    {
        $filter = new GeoDistance('location', array('lat' => 48.86, 'lon' => 2.35), '10km');
        $optimizeBbox = GeoDistance::OPTIMIZE_BBOX_MEMORY;
        $filter->setOptimizeBbox($optimizeBbox);

        $data = $filter->toArray();

        $this->assertEquals($optimizeBbox, $data['geo_distance']['optimize_bbox']);
    }
}
