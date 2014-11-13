<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Terms;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\ReverseNested;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class ReverseNestedTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("nested");
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "comments" => array(
                "type" => "nested",
                "properties" => array(
                    "name" => array("type" => "string"),
                    "body" => array("type" => "string")
                )
            )
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array(
                "comments" => array(
                    array(
                        "name" => "bob",
                        "body" => "this is bobs comment",
                    ),
                    array(
                        "name" => "john",
                        "body" => "this is johns comment",
                    ),
                ),
                "tags" => array("foo", "bar"),
            )),
            new Document("2", array(
                 "comments" => array(
                    array(
                        "name" => "bob",
                        "body" => "this is another comment from bob",
                    ),
                    array(
                        "name" => "susan",
                        "body" => "this is susans comment",
                    ),
                ),
                "tags" => array("foo", "baz"),
            ))
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testPathNotSetIfNull()
    {
        $agg = new ReverseNested('nested');
        $this->assertFalse($agg->hasParam('path'));
    }

    public function testPathSetIfNotNull()
    {
        $agg = new ReverseNested('nested', 'some_field');
        $this->assertEquals('some_field', $agg->getParam('path'));
    }

    public function testReverseNestedAggregation()
    {
        $agg = new Nested("comments", "comments");
        $names = new Terms("name");
        $names->setField("comments.name");

        $tags = new Terms("tags");
        $tags->setField("tags");

        $reverseNested = new ReverseNested("main");
        $reverseNested->addAggregation($tags);

        $names->addAggregation($reverseNested);

        $agg->addAggregation($names);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("comments");

        $this->assertArrayHasKey('name', $results);
        $nameResults = $results['name'];

        $this->assertCount(3, $nameResults['buckets']);

        // bob
        $this->assertEquals('bob', $nameResults['buckets'][0]['key']);
        $tags = array(
            array('key' => 'foo', 'doc_count' => 2),
            array('key' => 'bar', 'doc_count' => 1),
            array('key' => 'baz', 'doc_count' => 1),
        );
        $this->assertEquals($tags, $nameResults['buckets'][0]['main']['tags']['buckets']);

        // john
        $this->assertEquals('john', $nameResults['buckets'][1]['key']);
        $tags = array(
            array('key' => 'bar', 'doc_count' => 1),
            array('key' => 'foo', 'doc_count' => 1),
        );
        $this->assertEquals($tags, $nameResults['buckets'][1]['main']['tags']['buckets']);

        // susan
        $this->assertEquals('susan', $nameResults['buckets'][2]['key']);
        $tags = array(
            array('key' => 'baz', 'doc_count' => 1),
            array('key' => 'foo', 'doc_count' => 1),
        );
        $this->assertEquals($tags, $nameResults['buckets'][2]['main']['tags']['buckets']);
    }
}
