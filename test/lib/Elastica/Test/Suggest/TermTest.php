<?php

namespace Elastica\Test\Suggest;

use Elastica\Test\Base as BaseTest;
use Elastica\Suggest\Term;
use Elastica\Query;
use Elastica\Document;
use Elastica\Search;
use Elastica\Index;

class TermTest extends BaseTest
{
    public function testToArrayOneTerm()
    {
        $suggest = new Term();
        $suggest->addTerm('suggest1', array('text' => 'Foor', 'term' => array('field' => '_all', 'size' => 4)));

        $query = new Query();
        $query->addSuggest($suggest);

        $expectedArray = array(
                'suggest1' => array(
                    'text' => 'Foor',
                    'term' => array(
                        'field' => '_all',
                        'size' => 4)
                    )
                );
        $this->assertEquals($expectedArray, $suggest->toArray());
    }

    public function testToArrayMultipleTerms()
    {
        $suggest = new Term();
        $suggest->addTerm('suggest1', array('text' => 'Foor', 'term' => array('field' => '_all', 'size' => 4)));
        $suggest->addTerm('suggest2', array('text' => 'Fool', 'term' => array('field' => '_all', 'size' => 4)));

        $query = new Query();
        $query->addSuggest($suggest);

        $expectedArray = array(
            'suggest1' => array(
                    'text' => 'Foor',
                    'term' => array(
                        'field' => '_all',
                        'size' => 4)
                    ),
            'suggest2' => array(
                    'text' => 'Fool',
                    'term' => array(
                        'field' => '_all',
                        'size' => 4)
                    )
            );

        $this->assertEquals($expectedArray, $suggest->toArray());
    }

    public function testSuggestResults()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test_suggest');
        $search = new Search($client);

        $index = $client->getIndex('test_suggest');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Document(5, array('id' => 1, 'text' => 'GitHub'));
        $docs[] = new Document(6, array('id' => 1, 'text' => 'Elastic'));
        $docs[] = new Document(7, array('id' => 1, 'text' => 'Search'));
        $docs[] = new Document(3, array('id' => 1, 'text' => 'Food'));
        $docs[] = new Document(4, array('id' => 1, 'text' => 'Folks'));
        $type = $index->getType('testSuggestType');
        $type->addDocuments($docs);
        $index->refresh();

        $search->addIndex($index)->addType($type);

        $suggest = new Term();
        $suggest->addTerm('suggest1', array('text' => 'Foor', 'term' => array('field' => '_all', 'size' => 4)));
        $suggest->addTerm('suggest2', array('text' => 'Girhub', 'term' => array('field' => '_all', 'size' => 4)));

        $search->addSuggest($suggest);
        $result = $search->search();

        $this->assertEquals(2, $result->countSuggests());
        
        $suggests = $result->getSuggests();

        $this->assertEquals('github', $suggests['suggest2']['options'][0]['text']);
        $this->assertEquals('food', $suggests['suggest1']['options'][0]['text']);
    }

    public function testSuggestNoResults()
    {
        $client = $this->_getClient();
        $search = new Search($client);
        $index = new Index($client, 'test_suggest');

        $index = $client->getIndex('test_suggest');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Document(5, array('id' => 1, 'text' => 'GitHub'));
        $docs[] = new Document(6, array('id' => 1, 'text' => 'Elastic'));
        $docs[] = new Document(3, array('id' => 1, 'text' => 'Food'));
        $docs[] = new Document(4, array('id' => 1, 'text' => 'Folks'));
        $type = $index->getType('testSuggestType');
        $type->addDocuments($docs);
        $index->refresh();

        $search->addIndex($index)->addType($type);

        $suggest = new Term();
        $suggest->addTerm('suggest1', array('text' => 'Search', 'term' => array('field' => '_all', 'size' => 4)));

        $search->addSuggest($suggest);
        $result = $search->search();

        $this->assertEquals(0, $result->countSuggests());
    }
}
