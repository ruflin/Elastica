<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\SimpleQueryString;
use Elastica\Test\Base;

/**
 * @internal
 */
class SimpleQueryStringTest extends Base
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $string = 'this is a test';
        $fields = ['field1', 'field2'];
        $query = new SimpleQueryString($string, $fields);
        $query->setDefaultOperator(SimpleQueryString::OPERATOR_OR);
        $query->setAnalyzer('whitespace');

        $expected = [
            'simple_query_string' => [
                'query' => $string,
                'fields' => $fields,
                'analyzer' => 'whitespace',
                'default_operator' => SimpleQueryString::OPERATOR_OR,
            ],
        ];

        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testQuery(): void
    {
        $index = $this->_createIndex();
        $docs = [
            new Document(1, ['make' => 'Gibson', 'model' => 'Les Paul']),
            new Document(2, ['make' => 'Gibson', 'model' => 'SG Standard']),
            new Document(3, ['make' => 'Gibson', 'model' => 'SG Supreme']),
            new Document(4, ['make' => 'Gibson', 'model' => 'SG Faded']),
            new Document(5, ['make' => 'Fender', 'model' => 'Stratocaster']),
        ];
        $index->addDocuments($docs);
        $index->refresh();

        $query = new SimpleQueryString('gibson +sg +-faded', ['make', 'model']);
        $results = $index->search($query);

        $this->assertEquals(2, $results->getTotalHits());

        $query->setFields(['model']);
        $results = $index->search($query);

        // We should not get any hits, since the "make" field was not included in the query.
        $this->assertEquals(0, $results->getTotalHits());
    }

    /**
     * @group unit
     */
    public function testSetMinimumShouldMatch(): void
    {
        $expected = [
            'simple_query_string' => [
                'query' => 'DONT PANIC',
                'minimum_should_match' => '75%',
            ],
        ];

        $query = new SimpleQueryString($expected['simple_query_string']['query']);
        $query->setMinimumShouldMatch($expected['simple_query_string']['minimum_should_match']);

        $this->assertEquals($expected, $query->toArray());
        $this->assertInstanceOf(SimpleQueryString::class, $query->setMinimumShouldMatch('75%'));
    }

    /**
     * @group functional
     */
    public function testSetMinimumShouldMatchWorks(): void
    {
        $this->_checkVersion('1.5');

        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['body' => 'foo']),
            new Document(2, ['body' => 'bar']),
            new Document(3, ['body' => 'foo bar']),
            new Document(4, ['body' => 'foo baz bar']),
        ]);
        $index->refresh();

        $query = new SimpleQueryString('foo bar');
        $query->setMinimumShouldMatch(2);
        $results = $index->search($query);

        $this->assertCount(2, $results);
        $this->assertEquals(3, $results[0]->getId());
        $this->assertEquals(4, $results[1]->getId());
    }
}
