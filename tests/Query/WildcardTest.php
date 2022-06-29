<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query\Wildcard;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class WildcardTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstruct(): void
    {
        $wildcard = new Wildcard('name', 'aaa*');

        $data = $wildcard->getParam('name');
        $this->assertIsArray($data);

        $this->assertSame('aaa*', $data['value']);
        $this->assertSame(1.0, $data['boost']);
    }

    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $wildcard = new Wildcard('name', 'value*', 2.0);
        $wildcard->setRewrite(Wildcard::REWRITE_SCORING_BOOLEAN);

        $expectedArray = [
            'wildcard' => [
                'name' => [
                    'value' => 'value*',
                    'boost' => 2.0,
                    'rewrite' => 'scoring_boolean',
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $wildcard->toArray());
    }

    /**
     * @group functional
     */
    public function testSearchWithAnalyzer(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $indexParams = [
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'lw' => [
                            'type' => 'custom',
                            'tokenizer' => 'keyword',
                            'filter' => ['lowercase'],
                        ],
                    ],
                ],
            ],
        ];

        $index->create($indexParams, ['recreate' => true]);

        $mapping = new Mapping([
            'name' => ['type' => 'text', 'analyzer' => 'lw'],
        ]);
        $index->setMapping($mapping);

        $index->addDocuments([
            new Document('1', ['name' => 'Basel-Stadt']),
            new Document('2', ['name' => 'New York']),
            new Document('3', ['name' => 'Baden']),
            new Document('4', ['name' => 'Baden Baden']),
            new Document('5', ['name' => 'New Orleans']),
        ]);

        $index->refresh();

        $query = new Wildcard('name', 'ba*');
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());

        $query = new Wildcard('name', 'baden*');
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query = new Wildcard('name', 'baden b*');
        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());

        $query = new Wildcard('name', 'baden bas*');
        $resultSet = $index->search($query);

        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     * @dataProvider caseInsensitiveDataProvider
     */
    public function testCaseInsensitive(bool $expected): void
    {
        // feature doesn't exist on version prior 7.10;
        $this->_checkVersion('7.10');

        $expectedArray = [
            'wildcard' => [
                'name' => [
                    'value' => 'exampl*',
                    'boost' => 1.0,
                    'case_insensitive' => $expected,
                ],
            ],
        ];

        $query = new Wildcard('name', 'exampl*', 1.0);
        $query->setCaseInsensitive($expected);
        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function caseInsensitiveDataProvider(): iterable
    {
        yield [true];
        yield [false];
    }
}
