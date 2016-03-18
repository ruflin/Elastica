<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\GeohashCell;
use Elastica\Query;
use Elastica\Test\DeprecatedClassBase as BaseTest;
use Elastica\Type\Mapping;

class GeohashCellTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new GeohashCell('pin', array('lat' => 37.789018, 'lon' => -122.391506), '50m'));
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
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

    /**
     * @group functional
     */
    public function testFilter()
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

        $filter = new GeohashCell('pin', array('lat' => 32.828326, 'lon' => -117.255854));
        $query = new Query();
        $query->setPostFilter($filter);
        $results = $type->search($query);

        $this->assertEquals(1, $results->count());

        //test precision parameter
        $filter = new GeohashCell('pin', '9', 1);
        $query = new Query();
        $query->setPostFilter($filter);
        $results = $type->search($query);

        $this->assertEquals(2, $results->count());

        $index->delete();
    }
}
