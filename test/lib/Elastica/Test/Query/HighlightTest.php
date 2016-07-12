<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class HighlightTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testHightlightSearch()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $phrase = 'My name is ruflin';

        $type->addDocuments([
            new Document(1, ['id' => 1, 'phrase' => $phrase, 'username' => 'hanswurst', 'test' => ['2', '3', '5']]),
            new Document(2, ['id' => 2, 'phrase' => $phrase, 'username' => 'peter', 'test' => ['2', '3', '5']]),
        ]);

        $matchQuery = new Query\MatchPhrase('phrase', 'ruflin');
        $query = new Query($matchQuery);
        $query->setHighlight([
            'pre_tags' => ['<em class="highlight">'],
            'post_tags' => ['</em>'],
            'fields' => [
                'phrase' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ],
            ],
        ]);

        $index->refresh();

        $resultSet = $type->search($query);

        foreach ($resultSet as $result) {
            $highlight = $result->getHighlights();
            $this->assertEquals(['phrase' => [0 => 'My name is <em class="highlight">ruflin</em>']], $highlight);
        }
        $this->assertEquals(2, $resultSet->count());
    }
}
