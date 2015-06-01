<?php
namespace Elastica\Test\Suggest;

use Elastica\Document;
use Elastica\Index;
use Elastica\Suggest;
use Elastica\Suggest\Term;
use Elastica\Test\Base as BaseTest;

class TermTest extends BaseTest
{
    /**
     * @return Index
     */
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('testSuggestType');
        $type->addDocuments(array(
            new Document(1, array('id' => 1, 'text' => 'GitHub')),
            new Document(2, array('id' => 1, 'text' => 'Elastic')),
            new Document(3, array('id' => 1, 'text' => 'Search')),
            new Document(4, array('id' => 1, 'text' => 'Food')),
            new Document(5, array('id' => 1, 'text' => 'Flood')),
            new Document(6, array('id' => 1, 'text' => 'Folks')),
        ));
        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
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
                        'field' => '_all',
                    ),
                    'text' => 'Foor',
                ),
                'suggest2' => array(
                    'term' => array(
                        'field' => '_all',
                    ),
                    'text' => 'Girhub',
                ),
            ),
        );

        $this->assertEquals($expected, $suggest->toArray());
    }

    /**
     * @group functional
     */
    public function testSuggestResults()
    {
        $suggest = new Suggest();
        $suggest1 = new Term('suggest1', '_all');
        $suggest->addSuggestion($suggest1->setText('Foor seach'));
        $suggest2 = new Term('suggest2', '_all');
        $suggest->addSuggestion($suggest2->setText('Girhub'));

        $index = $this->_getIndexForTest();
        $result = $index->search($suggest);

        $this->assertEquals(2, $result->countSuggests());

        $suggests = $result->getSuggests();

        // Ensure that two suggestion results are returned for suggest1
        $this->assertEquals(2, sizeof($suggests['suggest1']));

        $this->assertEquals('github', $suggests['suggest2'][0]['options'][0]['text']);
        $this->assertEquals('food', $suggests['suggest1'][0]['options'][0]['text']);
    }

    /**
     * @group functional
     */
    public function testSuggestNoResults()
    {
        $termSuggest = new Term('suggest1', '_all');
        $termSuggest->setText('Foobar')->setSize(4);

        $index = $this->_getIndexForTest();
        $result = $index->search($termSuggest);

        $this->assertEquals(1, $result->countSuggests());

        // Assert that no suggestions were returned
        $suggests = $result->getSuggests();
        $this->assertEquals(0, sizeof($suggests['suggest1'][0]['options']));
    }
}
