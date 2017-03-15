<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\MatchPhrase;
use Elastica\Test\Base as BaseTest;

class MatchPhraseTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $field = 'test';
        $testQuery = 'Nicolas Ruflin';
        $analyzer = 'myanalyzer';
        $boost = 2.0;

        $query = new MatchPhrase();
        $query->setFieldQuery($field, $testQuery);
        $query->setFieldAnalyzer($field, $analyzer);
        $query->setFieldBoost($field, $boost);

        $expectedArray = [
            'match_phrase' => [
                $field => [
                    'query' => $testQuery,
                    'analyzer' => $analyzer,
                    'boost' => $boost,
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testMatchPhrase()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('test');

        $type->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $query = new MatchPhrase();
        $query->setFieldQuery('name', 'New York');

        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }
}
