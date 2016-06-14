<?php
namespace Elastica\Test\Suggest;

use Elastica\Document;
use Elastica\Index;
use Elastica\Suggest;
use Elastica\Suggest\CandidateGenerator\DirectGenerator;
use Elastica\Suggest\Phrase;
use Elastica\Test\Base as BaseTest;

class PhraseTest extends BaseTest
{
    /**
     * @return Index
     */
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('testSuggestType');
        $type->addDocuments(array(
            new Document(1, array('text' => 'Github is pretty cool')),
            new Document(2, array('text' => 'Elasticsearch is bonsai cool')),
            new Document(3, array('text' => 'This is a test phrase')),
            new Document(4, array('text' => 'Another sentence for testing')),
            new Document(5, array('text' => 'Some more words here')),
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
        $phraseSuggest = new Phrase('suggest1', 'text');
        $phraseSuggest->setText('elasticsearch is bansai coor');
        $phraseSuggest->setAnalyzer('simple');
        $suggest->addSuggestion($phraseSuggest);
        $suggest->setGlobalText('global!');

        $expected = array(
            'suggest' => array(
                'text' => 'global!',
                'suggest1' => array(
                    'text' => 'elasticsearch is bansai coor',
                    'phrase' => array(
                        'field' => 'text',
                        'analyzer' => 'simple',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $suggest->toArray());
    }

    /**
     * @group functional
     */
    public function testPhraseSuggest()
    {
        $suggest = new Suggest();
        $phraseSuggest = new Phrase('suggest1', 'text');
        $phraseSuggest->setText('elasticsearch is bansai coor');
        $phraseSuggest->setAnalyzer('simple')->setHighlight('<suggest>', '</suggest>')->setStupidBackoffSmoothing(0.4);
        $phraseSuggest->addCandidateGenerator(new DirectGenerator('text'));
        $suggest->addSuggestion($phraseSuggest);

        $index = $this->_getIndexForTest();
        $result = $index->search($suggest);
        $suggests = $result->getSuggests();

        // 3 suggestions should be returned: One in which both misspellings are corrected, and two in which only one misspelling is corrected.
        $this->assertEquals(3, sizeof($suggests['suggest1'][0]['options']));

        $this->assertEquals('elasticsearch is <suggest>bonsai cool</suggest>', $suggests['suggest1'][0]['options'][0]['highlighted']);
        $this->assertEquals('elasticsearch is bonsai cool', $suggests['suggest1'][0]['options'][0]['text']);
    }
}
