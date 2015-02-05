<?php

namespace Elastica\Test\Query;

use Elastica\Query\Common;
use Elastica\Test\Base as BaseTest;

class CommonTest extends BaseTest
{
    public function testToArray()
    {
        $query = new Common('body', 'test query', .001);
        $query->setLowFrequencyOperator(Common::OPERATOR_AND);

        $expected = array(
            'common' => array(
                'body' => array(
                    'query' => 'test query',
                    'cutoff_frequency' => .001,
                    'low_freq_operator' => 'and',
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());
    }

    public function testQuery()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        //add documents to create common terms
        $docs = array();
        for ($i = 0; $i < 20; $i++) {
            $docs[] = new \Elastica\Document($i, array('body' => 'foo bar'));
        }
        $type->addDocuments($docs);

        $type->addDocument(new \Elastica\Document(20, array('body' => 'foo baz')));
        $type->addDocument(new \Elastica\Document(21, array('body' => 'foo bar baz')));
        $type->addDocument(new \Elastica\Document(22, array('body' => 'foo bar baz bat')));
        $index->refresh();

        $query = new Common('body', 'foo bar baz bat', .5);
        $results = $type->search($query)->getResults();

        //documents containing only common words should not be returned
        $this->assertEquals(3, sizeof($results));

        $query->setMinimumShouldMatch(2);
        $results = $type->search($query);

        //only the document containing both low frequency terms should match
        $this->assertEquals(1, $results->count());

        $index->delete();
    }
}
