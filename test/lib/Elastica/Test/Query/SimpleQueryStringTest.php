<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\SimpleQueryString;
use Elastica\Test\Base;

class SimpleQueryStringTest extends Base
{
    /**
     * @var Index
     */
    protected $_index;

    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $docs = array(
            new Document(1, array('make' => 'Gibson', 'model' => 'Les Paul')),
            new Document(2, array('make' => 'Gibson', 'model' => 'SG Standard')),
            new Document(3, array('make' => 'Gibson', 'model' => 'SG Supreme')),
            new Document(4, array('make' => 'Gibson', 'model' => 'SG Faded')),
            new Document(5, array('make' => 'Fender', 'model' => 'Stratocaster')),
        );
        $this->_index->getType("guitars")->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testToArray()
    {
        $string = "this is a test";
        $fields = array('field1', 'field2');
        $query = new SimpleQueryString($string, $fields);
        $query->setDefaultOperator(SimpleQueryString::OPERATOR_OR);
        $query->setAnalyzer("whitespace");

        $expected = array(
            "simple_query_string" => array(
                "query" => $string,
                "fields" => $fields,
                "analyzer" => "whitespace",
                "default_operator" => SimpleQueryString::OPERATOR_OR,
            ),
        );

        $this->assertEquals($expected, $query->toArray());
    }

    public function testQuery()
    {
        $query = new SimpleQueryString("gibson +sg +-faded", array("make", "model"));
        $results = $this->_index->search($query);

        $this->assertEquals(2, $results->getTotalHits());

        $query->setFields(array("model"));
        $results = $this->_index->search($query);

        // We should not get any hits, since the "make" field was not included in the query.
        $this->assertEquals(0, $results->getTotalHits());
    }
}
