<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\QueryString;
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

        $type->addDocuments(array(
            new Document(1, array('id' => 1, 'phrase' => $phrase, 'username' => 'hanswurst', 'test' => array('2', '3', '5'))),
            new Document(2, array('id' => 2, 'phrase' => $phrase, 'username' => 'peter', 'test' => array('2', '3', '5'))),
        ));

        $queryString = new QueryString('rufl*');
        $query = new Query($queryString);
        $query->setHighlight(array(
            'pre_tags' => array('<em class="highlight">'),
            'post_tags' => array('</em>'),
            'fields' => array(
                'phrase' => array(
                    'fragment_size' => 200,
                    'number_of_fragments' => 1,
                ),
            ),
        ));

        $index->refresh();

        $resultSet = $type->search($query);
        foreach ($resultSet as $result) {
            $highlight = $result->getHighlights();
            $this->assertEquals(array('phrase' => array(0 => 'My name is <em class="highlight">ruflin</em>')), $highlight);
        }
        $this->assertEquals(2, $resultSet->count());
    }
}
