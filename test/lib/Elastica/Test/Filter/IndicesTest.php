<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\BoolNot;
use Elastica\Filter\Indices;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class IndicesTest extends BaseTest
{
    /**
     * @var Index
     */
    protected $_index1;

    /**
     * @var Index
     */
    protected $_index2;

    protected function setUp()
    {
        parent::setUp();
        $this->_index1 = $this->_createIndex('indices_filter_1');
        $this->_index2 = $this->_createIndex('indices_filter_2');
        $this->_index1->addAlias("indices_filter");
        $this->_index2->addAlias("indices_filter");
        $docs = array(
            new Document("1", array("color" => "blue")),
            new Document("2", array("color" => "green")),
            new Document("3", array("color" => "blue")),
            new Document("4", array("color" => "yellow")),
        );
        $this->_index1->getType("test")->addDocuments($docs);
        $this->_index2->getType("test")->addDocuments($docs);
        $this->_index1->refresh();
        $this->_index2->refresh();
    }

    protected function tearDown()
    {
        $this->_index1->delete();
        $this->_index2->delete();
        parent::tearDown();
    }

    public function testToArray()
    {
        $expected = array(
            "indices" => array(
                "indices" => array("index1", "index2"),
                "filter" => array(
                    "term" => array("tag" => "wow")
                ),
                "no_match_filter" => array(
                    "term" => array("tag" => "such filter")
                )
            )
        );
        $filter = new Indices(new Term(array("tag" => "wow")), array("index1", "index2"));
        $filter->setNoMatchFilter(new Term(array("tag" => "such filter")));
        $this->assertEquals($expected, $filter->toArray());
    }

    public function testIndicesFilter()
    {
        $filter = new Indices(new BoolNot(new Term(array("color" => "blue"))), array($this->_index1->getName()));
        $filter->setNoMatchFilter(new BoolNot(new Term(array("color" => "yellow"))));
        $query = new Query();
        $query->setPostFilter($filter);

        // search over the alias
        $index = $this->_getClient()->getIndex("indices_filter");
        $results = $index->search($query);

        // ensure that the proper docs have been filtered out for each index
        $this->assertEquals(5, $results->count());
        foreach ($results->getResults() as $result) {
            $data = $result->getData();
            $color = $data["color"];
            if ($result->getIndex() == $this->_index1->getName()) {
                $this->assertNotEquals("blue", $color);
            } else {
                $this->assertNotEquals("yellow", $color);
            }
        }
    }
}
 