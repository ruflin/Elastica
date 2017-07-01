<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\GeoPolygon;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class GeoPolygonTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGeoPoint()
    {
        $index = $this->_createIndex();

        $type = $index->getType('test');

        // Set mapping
        $type->setMapping(['location' => ['type' => 'geo_point']]);

        // Add doc 1
        $doc1 = new Document(1,
            [
                'name' => 'ruflin',
            ]
        );

        $doc1->addGeoPoint('location', 17, 19);
        $type->addDocument($doc1);

        // Add doc 2
        $doc2 = new Document(2,
            [
                'name' => 'ruflin',
            ]
        );

        $doc2->addGeoPoint('location', 30, 40);
        $type->addDocument($doc2);

        $index->refresh();

        // Only one point should be in polygon
        $points = [[16, 16], [16, 20], [20, 20], [20, 16], [16, 16]];
        $geoQuery = new GeoPolygon('location', $points);

        $query = new Query(new MatchAll());
        $query->setPostFilter($geoQuery);
        $this->assertEquals(1, $type->search($query)->count());

        // Both points should be inside
        $query = new Query();
        $points = [[16, 16], [16, 40], [40, 40], [40, 16], [16, 16]];
        $geoQuery = new GeoPolygon('location', $points);

        $query = new Query(new MatchAll());
        $query->setPostFilter($geoQuery);

        $this->assertEquals(2, $type->search($query)->count());
    }
}
