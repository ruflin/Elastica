<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_GeoDistanceTest extends PHPUnit_Framework_TestCase
{
    public function testGeoPoint()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);

        $type = $index->getType('test');

        // Set mapping
        $type->setMapping(array('point' => array('type' => 'geo_point')));

        // Add doc 1
        $doc1 = new Elastica_Document(1,
            array(
                'name' => 'ruflin',
            )
        );

        $doc1->addGeoPoint('point', 17, 19);
        $type->addDocument($doc1);

        // Add doc 2
        $doc2 = new Elastica_Document(2,
            array(
                'name' => 'ruflin',
            )
        );

        $doc2->addGeoPoint('point', 30, 40);
        $type->addDocument($doc2);

        $index->optimize();
        $index->refresh();

        // Only one point should be in radius
        $query = new Elastica_Query();
        $geoFilter = new Elastica_Filter_GeoDistance('point', array('lat' => 30, 'lon' => 40), '1km');

        $query = new Elastica_Query(new Elastica_Query_MatchAll());
        $query->setFilter($geoFilter);
        $this->assertEquals(1, $type->search($query)->count());

        // Both points should be inside
        $query = new Elastica_Query();
        $geoFilter = new Elastica_Filter_GeoDistance('point', array('lat' => 30, 'lon' => 40), '40000km');
        $query = new Elastica_Query(new Elastica_Query_MatchAll());
        $query->setFilter($geoFilter);
        $index->refresh();

        $this->assertEquals(2, $type->search($query)->count());
    }

    public function testConstructLatlon()
    {
        $key = 'location';
        $location = array(
            'lat' => 48.86,
            'lon' => 2.35
        );
        $distance = '10km';

        $filter = new Elastica_Filter_GeoDistance($key, $location, $distance);

        $expected = array(
            'geo_distance' => array(
                $key => $location,
                'distance' => $distance
            )
        );

        $data = $filter->toArray();

        $this->assertEquals($expected, $data);
    }

    public function testConstructGeohash()
    {
        $key = 'location';
        $location = 'u09tvqx';
        $distance = '10km';

        $filter = new Elastica_Filter_GeoDistance($key, $location, $distance);

        $expected = array(
            'geo_distance' => array(
                $key => $location,
                'distance' => $distance
            )
        );

        $data = $filter->toArray();

        $this->assertEquals($expected, $data);
    }

    public function testConstructOldSignature()
    {
        $key = 'location';
        $latitude = 48.86;
        $longitude = 2.35;
        $distance = '10km';

        $filter = new Elastica_Filter_GeoDistance($key, $latitude, $longitude, $distance);

        $expected = array(
            'geo_distance' => array(
                $key => array(
                    'lat' => $latitude,
                    'lon' => $longitude
                ),
                'distance' => $distance
            )
        );

        $data = $filter->toArray();

        $this->assertEquals($expected, $data);
    }

    public function testSetDistanceType()
    {
        $filter = new Elastica_Filter_GeoDistance('location', array('lat' => 48.86, 'lon' => 2.35), '10km');
        $distanceType = Elastica_Filter_GeoDistance::DISTANCE_TYPE_ARC;
        $filter->setDistanceType($distanceType);

        $data = $filter->toArray();

        $this->assertEquals($distanceType, $data['geo_distance']['distance_type']);
    }

    public function testSetOptimizeBbox()
    {
        $filter = new Elastica_Filter_GeoDistance('location', array('lat' => 48.86, 'lon' => 2.35), '10km');
        $optimizeBbox = Elastica_Filter_GeoDistance::OPTIMIZE_BBOX_MEMORY;
        $filter->setOptimizeBbox($optimizeBbox);

        $data = $filter->toArray();

        $this->assertEquals($optimizeBbox, $data['geo_distance']['optimize_bbox']);
    }
}
