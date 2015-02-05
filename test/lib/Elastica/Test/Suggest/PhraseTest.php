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
    const TEST_TYPE = 'testSuggestType';

    /**
     * @var Index
     */
    protected $_index;

    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $docs = array();
        $docs[] = new Document(1, array('text' => 'Github is pretty cool'));
        $docs[] = new Document(2, array('text' => 'Elasticsearch is bonsai cool'));
        $docs[] = new Document(3, array('text' => 'This is a test phrase'));
        $docs[] = new Document(4, array('text' => 'Another sentence for testing'));
        $docs[] = new Document(5, array('text' => 'Some more words here'));
        $type = $this->_index->getType(self::TEST_TYPE);
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

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

    public function testPhraseSuggest()
    {
        $suggest = new Suggest();
        $phraseSuggest = new Phrase('suggest1', 'text');
        $phraseSuggest->setText("elasticsearch is bansai coor");
        $phraseSuggest->setAnalyzer("simple")->setHighlight("<suggest>", "</suggest>")->setStupidBackoffSmoothing(0.4);
        $phraseSuggest->addCandidateGenerator(new DirectGenerator("text"));
        $suggest->addSuggestion($phraseSuggest);

        $result = $this->_index->search($suggest);
        $suggests = $result->getSuggests();

        // 3 suggestions should be returned: One in which both misspellings are corrected, and two in which only one misspelling is corrected.
        $this->assertEquals(3, sizeof($suggests['suggest1'][0]['options']));

        $this->assertEquals("elasticsearch is <suggest>bonsai cool</suggest>", $suggests['suggest1'][0]['options'][0]['highlighted']);
        $this->assertEquals("elasticsearch is bonsai cool", $suggests['suggest1'][0]['options'][0]['text']);
    }
}
