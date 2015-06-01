<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\SimpleQueryString;
use Elastica\Test\Base;

class SimpleQueryStringTest extends Base
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $string = 'this is a test';
        $fields = array('field1', 'field2');
        $query = new SimpleQueryString($string, $fields);
        $query->setDefaultOperator(SimpleQueryString::OPERATOR_OR);
        $query->setAnalyzer('whitespace');

        $expected = array(
            'simple_query_string' => array(
                'query' => $string,
                'fields' => $fields,
                'analyzer' => 'whitespace',
                'default_operator' => SimpleQueryString::OPERATOR_OR,
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
        $docs = array(
            new Document(1, array('make' => 'Gibson', 'model' => 'Les Paul')),
            new Document(2, array('make' => 'Gibson', 'model' => 'SG Standard')),
            new Document(3, array('make' => 'Gibson', 'model' => 'SG Supreme')),
            new Document(4, array('make' => 'Gibson', 'model' => 'SG Faded')),
            new Document(5, array('make' => 'Fender', 'model' => 'Stratocaster')),
        );
        $index->getType('guitars')->addDocuments($docs);
        $index->refresh();

        $query = new SimpleQueryString('gibson +sg +-faded', array('make', 'model'));
        $results = $index->search($query);

        $this->assertEquals(2, $results->getTotalHits());

        $query->setFields(array('model'));
        $results = $index->search($query);

        // We should not get any hits, since the "make" field was not included in the query.
        $this->assertEquals(0, $results->getTotalHits());
    }

    /**
     * @group unit
     */
    public function testSetMinimumShouldMatch()
    {
        $expected = array(
            'simple_query_string' => array(
                'query' => 'DONT PANIC',
                'minimum_should_match' => '75%',
            ),
        );

        $query = new SimpleQueryString($expected['simple_query_string']['query']);
        $query->setMinimumShouldMatch($expected['simple_query_string']['minimum_should_match']);

        $this->assertEquals($expected, $query->toArray());
        $this->assertInstanceOf('Elastica\Query\SimpleQueryString', $query->setMinimumShouldMatch('75%'));
    }

    /**
     * @group functional
     */
    public function testSetMinimumShouldMatchWorks()
    {
        $index = $this->_createIndex();
        $type = $index->getType('foobars');
        $type->addDocuments(array(
            new Document(1, array('body' => 'foo')),
            new Document(2, array('body' => 'bar')),
            new Document(3, array('body' => 'foo bar')),
            new Document(4, array('body' => 'foo baz bar')),
        ));
        $index->refresh();

        $query = new SimpleQueryString('foo bar');
        $query->setMinimumShouldMatch(2);
        $results = $type->search($query);

        $this->assertCount(2, $results);
        $this->assertEquals(3, $results[0]->getId());
        $this->assertEquals(4, $results[1]->getId());
    }
}
