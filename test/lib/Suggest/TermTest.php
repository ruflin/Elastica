<?php

namespace Elastica\Test\Suggest;

use Elastica\Suggest;
use Elastica\Suggest\Term;
use Elastica\Test\Base as BaseTest;
use Elastica\Query;
use Elastica\Document;
use Elastica\Index;

class TermTest extends BaseTest
{
    const TEST_TYPE = 'testSuggestType';

    /**
     * @var Index
     */
    protected $_index;

    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex('test_suggest');
        $docs = array();
        $docs[] = new Document(1, array('id' => 1, 'text' => 'GitHub'));
        $docs[] = new Document(2, array('id' => 1, 'text' => 'Elastic'));
        $docs[] = new Document(3, array('id' => 1, 'text' => 'Search'));
        $docs[] = new Document(4, array('id' => 1, 'text' => 'Food'));
        $docs[] = new Document(5, array('id' => 1, 'text' => 'Flood'));
        $docs[] = new Document(6, array('id' => 1, 'text' => 'Folks'));
        $type = $this->_index->getType(self::TEST_TYPE);
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    protected function tearDown()
    {
        $this->_index->delete();
    }

    public function testToArray()
    {
        $suggest = new Suggest();
        $suggest1 = new Term('suggest1', '_all');
        $suggest->addSuggestion($suggest1->setText('Foor'));
        $suggest2 = new Term('suggest2', '_all');
        $suggest->addSuggestion($suggest2->setText('Girhub'));

        $expected = array(
            'suggest' => array(
                'suggest1' => array(
                    'term' => array(
                        'field' => '_all'
                    ),
                    'text' => 'Foor'
                ),
                'suggest2' => array(
                    'term' => array(
                        'field' => '_all'
                    ),
                    'text' => 'Girhub'
                )
            )
        );

        $this->assertEquals($expected, $suggest->toArray());
    }

    public function testSuggestResults()
    {
        $suggest = new Suggest();
        $suggest1 = new Term('suggest1', '_all');
        $suggest->addSuggestion($suggest1->setText('Foor seach'));
        $suggest2 = new Term('suggest2', '_all');
        $suggest->addSuggestion($suggest2->setText('Girhub'));

        $result = $this->_index->search($suggest);

        $this->assertEquals(2, $result->countSuggests());

        $suggests = $result->getSuggests();

        // Ensure that two suggestion results are returned for suggest1
        $this->assertEquals(2, sizeof($suggests['suggest1']));

        $this->assertEquals('github', $suggests['suggest2'][0]['options'][0]['text']);
        $this->assertEquals('food', $suggests['suggest1'][0]['options'][0]['text']);
    }

    public function testSuggestNoResults()
    {
        $termSuggest = new Term('suggest1', '_all');
        $termSuggest->setText('Foobar')->setSize(4);

        $result = $this->_index->search($termSuggest);

        $this->assertEquals(1, $result->countSuggests());

        // Assert that no suggestions were returned
        $suggests = $result->getSuggests();
        $this->assertEquals(0, sizeof($suggests['suggest1'][0]['options']));
    }
}
