<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\GeohashCell;
use Elastica\Test\Base as BaseTest;

class GeohashCellTest extends BaseTest
{
    public function testToArray()
    {
        $filter = new GeohashCell('pin', array('lat' => 37.789018, 'lon' => -122.391506), '50m');
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
        $this->assertEquals($expected, $filter->toArray());
    }

    public function testFilter()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $mapping = new \Elastica\Type\Mapping($type, array(
            'pin' => array(
                'type' => 'geo_point',
                'geohash' => true,
                'geohash_prefix' => true,
            ),
        ));
        $type->setMapping($mapping);

        $type->addDocument(new \Elastica\Document(1, array('pin' => '9q8yyzm0zpw8')));
        $type->addDocument(new \Elastica\Document(2, array('pin' => '9mudgb0yued0')));
        $index->refresh();

        $filter = new GeohashCell('pin', array('lat' => 32.828326, 'lon' => -117.255854));
        $query = new \Elastica\Query();
        $query->setPostFilter($filter);
        $results = $type->search($query);

        $this->assertEquals(1, $results->count());

        //test precision parameter
        $filter = new GeohashCell('pin', '9', 1);
        $query = new \Elastica\Query();
        $query->setPostFilter($filter);
        $results = $type->search($query);

        $this->assertEquals(2, $results->count());

        $index->delete();
    }
}
