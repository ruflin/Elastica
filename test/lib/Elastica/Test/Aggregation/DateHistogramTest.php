<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class DateHistogramTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "created" => array("type" => "date"),
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array("created" => "2014-01-29T00:20:00")),
            new Document("2", array("created" => "2014-01-29T02:20:00")),
            new Document("3", array("created" => "2014-01-29T03:20:00")),
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testDateHistogramAggregation()
    {
        $agg = new DateHistogram("hist", "created", "1h");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("hist");

        $this->assertEquals(3, sizeof($results['buckets']));
    }

    public function testSetOffset()
    {
        $agg = new DateHistogram('hist', 'created', '1h');

        $agg->setOffset('3m');

        $expected = array(
            'date_histogram' => array(
                'field' => 'created',
                'interval' => '1h',
                'offset' => '3m',
            ),
        );

       $this->assertEquals($expected, $agg->toArray());

       $this->assertInstanceOf('Elastica\Aggregation\DateHistogram', $agg->setOffset('3m'));
    }

    public function testSetOffsetWorks()
    {
        $agg = new DateHistogram('hist', 'created', '1m');
        $agg->setOffset('+40s');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation('hist');

        $this->assertEquals('2014-01-29T00:19:40.000Z', $results['buckets'][0]['key_as_string']);
    }

    public function testSetTimezone()
    {
        $agg = new DateHistogram('hist', 'created', '1h');

        $agg->setTimezone('-02:30');

        $expected = array(
            'date_histogram' => array(
                'field' => 'created',
                'interval' => '1h',
                'time_zone' => '-02:30',
            ),
        );

       $this->assertEquals($expected, $agg->toArray());

       $this->assertInstanceOf('Elastica\Aggregation\DateHistogram', $agg->setTimezone('-02:30'));
    }
}
