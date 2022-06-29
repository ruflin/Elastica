<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Common;
use Elastica\Test\Base as BaseTest;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class CommonTest extends BaseTest
{
    use ExpectDeprecationTrait;

    /**
     * @group unit
     * @group legacy
     */
    public function testToArray(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: The "Elastica\Query\Common" class is deprecated, use "Elastica\Query\MatchQuery" instead. It will be removed in 8.0.');

        $query = new Common('body', 'test query', .001);
        $query->setLowFrequencyOperator(Common::OPERATOR_AND);

        $expected = [
            'common' => [
                'body' => [
                    'query' => 'test query',
                    'cutoff_frequency' => .001,
                    'low_freq_operator' => 'and',
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     * @group legacy
     */
    public function testQuery(): void
    {
        $index = $this->_createIndex();

        $docs = [
            new Document('1', ['body' => 'foo baz']),
            new Document('2', ['body' => 'foo bar baz']),
            new Document('3', ['body' => 'foo bar baz bat']),
        ];
        // add documents to create common terms
        for ($i = 4; $i < 24; ++$i) {
            $docs[] = new Document((string) $i, ['body' => 'foo bar']);
        }
        $index->addDocuments($docs);
        $index->refresh();

        $query = new Common('body', 'foo bar baz bat', .5);
        $results = $index->search($query)->getResults();

        // documents containing only common words should not be returned
        $this->assertCount(3, $results);

        $query->setMinimumShouldMatch(2);
        $results = $index->search($query);

        // only the document containing both low frequency terms should match
        $this->assertEquals(1, $results->count());
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testSetHighFrequencyOperator(): void
    {
        $value = 'OPERATOR_TEST';
        $query = new Common('body', 'test query', .001);
        $query->setHighFrequencyOperator($value);

        $this->assertEquals($value, $query->toArray()['common']['body']['high_frequency_operator']);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testSetBoost(): void
    {
        $value = .02;
        $query = new Common('body', 'test query', .001);
        $query->setBoost($value);

        $this->assertEquals($value, $query->toArray()['common']['body']['boost']);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testSetAnalyzer(): void
    {
        $analyzer = 'standard';
        $query = new Common('body', 'test query', .001);
        $query->setBoost(.02);
        $query->setAnalyzer($analyzer);

        $this->assertEquals($analyzer, $query->toArray()['common']['body']['analyzer']);
    }
}
