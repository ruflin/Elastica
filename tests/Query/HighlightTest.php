<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\MatchPhrase;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class HighlightTest extends BaseTest
{
    #[Group('functional')]
    public function testHightlightSearch(): void
    {
        $index = $this->_createIndex();

        $phrase = 'My name is ruflin';

        $index->addDocuments([
            new Document('1', ['id' => 1, 'phrase' => $phrase, 'username' => 'hanswurst', 'test' => ['2', '3', '5']]),
            new Document('2', ['id' => 2, 'phrase' => $phrase, 'username' => 'peter', 'test' => ['2', '3', '5']]),
        ]);

        $matchQuery = new MatchPhrase('phrase', 'ruflin');
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

        $resultSet = $index->search($query);

        foreach ($resultSet as $result) {
            $highlight = $result->getHighlights();
            $this->assertEquals(['phrase' => [0 => 'My name is <em class="highlight">ruflin</em>']], $highlight);
        }
        $this->assertEquals(2, $resultSet->count());
    }
}
