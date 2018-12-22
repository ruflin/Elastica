<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Wildcard;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class WildcardTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstructEmpty()
    {
        $wildcard = new Wildcard();
        $this->assertEmpty($wildcard->getParams());
    }

    /**
     * @group unit
     */
    public function testToArray()
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
    public function testSearchWithAnalyzer()
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
        $type = $index->getType('_doc');

        $mapping = new Mapping($type, [
                'name' => ['type' => 'text', 'analyzer' => 'lw'],
            ]
        );
        $type->setMapping($mapping);

        $type->addDocuments([
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
