<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\DateRange;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class DateRangeTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("date_range");
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "created" => array("type" => "date")
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array("created" => 1390962135000)),
            new Document("2", array("created" => 1390965735000)),
            new Document("3", array("created" => 1390954935000)),
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testDateRangeAggregation()
    {
        $agg = new DateRange("date");
        $agg->setField("created");
        $agg->addRange(1390958535000)->addRange(null, 1390958535000);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("date");

        foreach ($results['buckets'] as $bucket) {
            if (array_key_exists('to', $bucket)) {
                $this->assertEquals(1, $bucket['doc_count']);
            } else if (array_key_exists('from', $bucket)) {
                $this->assertEquals(2, $bucket['doc_count']);
            }
        }
    }
}
 