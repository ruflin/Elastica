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
        $type->addDocuments([
            new Document(1, ['id' => 1, 'text' => 'GitHub']),
            new Document(2, ['id' => 1, 'text' => 'Elastic']),
            new Document(3, ['id' => 1, 'text' => 'Search']),
            new Document(4, ['id' => 1, 'text' => 'Food']),
            new Document(5, ['id' => 1, 'text' => 'Flood']),
            new Document(6, ['id' => 1, 'text' => 'Folks']),
        ]);
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
        $suggest1->setSort(Term::SORT_FREQUENCY);

        $suggest->addSuggestion($suggest1->setText('Foor'));

        $suggest2 = new Term('suggest2', '_all');
        $suggest2->setSuggestMode(Term::SUGGEST_MODE_POPULAR);
        $suggest->addSuggestion($suggest2->setText('Girhub'));

        $expected = [
            'suggest' => [
                'suggest1' => [
                    'term' => [
                        'field' => '_all',
                        'sort' => 'frequency',
                    ],
                    'text' => 'Foor',
                ],
                'suggest2' => [
                    'term' => [
                        'field' => '_all',
                        'suggest_mode' => 'popular',
                    ],
                    'text' => 'Girhub',
                ],
            ],
        ];

        $this->assertEquals($expected, $suggest->toArray());
    }

    /**
     * @group functional
     */
    public function testSuggestResults()
    {
        $suggest = new Suggest();
        $suggest1 = new Term('suggest1', 'text');
        $suggest->addSuggestion($suggest1->setText('Foor seach'));
        $suggest2 = new Term('suggest2', 'text');
        $suggest->addSuggestion($suggest2->setText('Girhub'));

        $index = $this->_getIndexForTest();
        $result = $index->search($suggest);

        $this->assertEquals(2, $result->countSuggests());

        $suggests = $result->getSuggests();

        // Ensure that two suggestion results are returned for suggest1
        $this->assertCount(2, $suggests['suggest1']);

        $this->assertEquals('github', $suggests['suggest2'][0]['options'][0]['text']);
        $this->assertEquals('food', $suggests['suggest1'][0]['options'][0]['text']);
    }

    /**
     * @group functional
     */
    public function testSuggestNoResults()
    {
        $termSuggest = new Term('suggest1', 'text');
        $termSuggest->setText('Foobar')->setSize(4);

        $index = $this->_getIndexForTest();
        $result = $index->search($termSuggest);

        $this->assertEquals(1, $result->countSuggests());

        // Assert that no suggestions were returned
        $suggests = $result->getSuggests();
        $this->assertCount(0, $suggests['suggest1'][0]['options']);
    }
}
