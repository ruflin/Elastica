<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\GeohashCell;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class GeohashCellTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new GeohashCell('pin', array('lat' => 37.789018, 'lon' => -122.391506), '50m');
        $expected = array(
            'geohash_cell' => array(
                'pin' => array(
                    'lat' => 37.789018,
                    'lon' => -122.391506,
                ),
                'precision' => '50m',
                'neighbors' => false,
            ),
        );
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testQuery()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $mapping = new Mapping($type, array(
            'pin' => array(
                'type' => 'geo_point',
                'geohash' => true,
                'geohash_prefix' => true,
            ),
        ));
        $type->setMapping($mapping);

        $type->addDocument(new Document(1, array('pin' => '9q8yyzm0zpw8')));
        $type->addDocument(new Document(2, array('pin' => '9mudgb0yued0')));
        $index->refresh();

        $geoQuery = new GeohashCell('pin', array('lat' => 32.828326, 'lon' => -117.255854));
        $query = new Query();
        $query->setPostFilter($geoQuery);
        $results = $type->search($query);

        $this->assertEquals(1, $results->count());

        //test precision parameter
        $geoQuery = new GeohashCell('pin', '9', 1);
        $query = new Query();
        $query->setPostFilter($geoQuery);
        $results = $type->search($query);

        $this->assertEquals(2, $results->count());

        $index->delete();
    }
}
