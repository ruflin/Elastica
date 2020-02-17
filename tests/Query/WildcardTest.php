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
    public function testConstructEmpty(): void
    {
        $wildcard = new Wildcard();
        $this->assertEmpty($wildcard->getParams());
    }

    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $key = 'name';
        $value = 'Ru*lin';
        $boost = 2.0;

        $wildcard = new Wildcard($key, $value, $boost);

        $expectedArray = [
            'wildcard' => [
                $key => [
                    'value' => $value,
                    'boost' => $boost,
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

        $index->create($indexParams, true);

        $mapping = new Mapping([
            'name' => ['type' => 'text', 'analyzer' => 'lw'],
        ]);
        $index->setMapping($mapping);

        $index->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'Baden']),
            new Document(4, ['name' => 'Baden Baden']),
            new Document(5, ['name' => 'New Orleans']),
        ]);

        $index->refresh();

        $query = new Wildcard();
        $query->setValue('name', 'ba*');
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());

        $query = new Wildcard();
        $query->setValue('name', 'baden*');
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query = new Wildcard();
        $query->setValue('name', 'baden b*');
        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());

        $query = new Wildcard();
        $query->setValue('name', 'baden bas*');
        $resultSet = $index->search($query);

        $this->assertEquals(0, $resultSet->count());
    }
}
