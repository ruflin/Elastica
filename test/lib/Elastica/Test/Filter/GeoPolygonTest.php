<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\GeoPolygon;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class GeoPolygonTest extends BaseTest
{
    public function testGeoPoint()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);

        $type = $index->getType('test');

        // Set mapping
        $type->setMapping(array('location' => array('type' => 'geo_point')));

        // Add doc 1
        $doc1 = new Document(1,
            array(
                'name' => 'ruflin',
            )
        );

        $doc1->addGeoPoint('location', 17, 19);
        $type->addDocument($doc1);

        // Add doc 2
        $doc2 = new Document(2,
            array(
                'name' => 'ruflin',
            )
        );

        $doc2->addGeoPoint('location', 30, 40);
        $type->addDocument($doc2);

        $index->refresh();

        // Only one point should be in polygon
        $query = new Query();
        $points = array(array(16, 16), array(16, 20), array(20, 20), array(20, 16), array(16, 16));
        $geoFilter = new GeoPolygon('location', $points);

        $query = new Query(new MatchAll());
        $query->setFilter($geoFilter);
        $this->assertEquals(1, $type->search($query)->count());

        // Both points should be inside
        $query = new Query();
        $points = array(array(16, 16), array(16, 40), array(40, 40), array(40, 16), array(16, 16));
        $geoFilter = new GeoPolygon('location', $points);

        $query = new Query(new MatchAll());
        $query->setFilter($geoFilter);

        $this->assertEquals(2, $type->search($query)->count());
    }
}
