<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query\Match;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class PostFilterTest extends BaseTest
{
    /**
     * @var Index
     */
    protected $_index;

    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("query");
        $docs = array(
            new Document("1", array("color" => "green", "make" => "ford")),
            new Document("2", array("color" => "blue", "make" => "volvo")),
            new Document("3", array("color" => "red", "make" => "ford")),
            new Document("4", array("color" => "green", "make" => "renault")),
        );
        $this->_index->getType("test")->addDocuments($docs);
        $this->_index->refresh();

    }

    protected function tearDown()
    {
        parent::tearDown();
        if ($this->_index instanceof Index) {
            $this->_index->delete();
        }
    }

    public function testToArray()
    {
        $query = new Query();

        $post_filter = new Term(array('color' => 'green'));
        $query->setPostFilter($post_filter);

        $data = $query->toArray();

        $this->assertArrayHasKey('post_filter', $data);
        $this->assertEquals(array('term' => array('color' => 'green')), $data['post_filter']);

    }

    public function testQuery()
    {
        $query = new Query();

        $match = new Match();
        $match->setField('make', 'ford');

        $query->setQuery($match);

        $filter = new Term();
        $filter->setTerm('color', 'green');

        $query->setPostFilter($filter);

        $results = $this->_index->search($query);

        $this->assertEquals(1, $results->getTotalHits());

    }

    protected function _createIndex($name = 'test', $delete = true, $shards = 1)
    {
        return parent::_createIndex('test_postfilter_' . $name, $delete, $shards);
    }
}
