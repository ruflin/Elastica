<?php

namespace Elastica\Test\Type;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class MappingTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testMappingStoreFields()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create([], true);
        $type = $index->getType('_doc');

        $mapping = new Mapping($type,
            [
                'firstname' => ['type' => 'text', 'store' => true],
                // default is store => no expected
                'lastname' => ['type' => 'text'],
            ]
        );
        $mapping->disableSource();

        $type->setMapping($mapping);

        $firstname = 'Nicolas';
        $doc = new Document(1,
            [
                'firstname' => $firstname,
                'lastname' => 'Ruflin',
            ]
        );

        $type->addDocument($doc);

        $index->refresh();
        $queryString = new QueryString('ruflin');
        $query = Query::create($queryString);
        $query->setStoredFields(['*']);

        $resultSet = $type->search($query);
        $result = $resultSet->current();
        $fields = $result->getFields();

        $this->assertEquals($firstname, $fields['firstname'][0]);
        $this->assertArrayNotHasKey('lastname', $fields);
        $this->assertCount(1, $fields);

        $index->flush();
        $document = $type->getDocument(1);

        $this->assertEmpty($document->getData());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testNestedMapping()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create([], true);
        $type = $index->getType('_doc');

        $mapping = new Mapping($type,
            [
                'test' => [
                    'type' => 'object', 'properties' => [
                        'user' => [
                            'properties' => [
                                'firstname' => ['type' => 'text', 'store' => true],
                                'lastname' => ['type' => 'text', 'store' => true],
                                'age' => ['type' => 'integer', 'store' => true],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $response = $type->setMapping($mapping);
        $this->assertFalse($response->hasError());

        $doc = new Document(1, [
            'user' => [
                'firstname' => 'Nicolas',
                'lastname' => 'Ruflin',
                'age' => 9,
            ],
        ]);

        $type->addDocument($doc);

        $index->refresh();
        $resultSet = $type->search('ruflin');
        $this->assertEquals($resultSet->count(), 1);

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testJoinMapping()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testjoinparentid');
        $index->create([], true);
        $type = $index->getType('_doc');

        $mapping = new Mapping();
        $mapping->setType($type);

        $mapping = new Mapping($type, [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $expected = [
            '_doc' => [
                'properties' => [
                    'firstname' => ['type' => 'text', 'store' => true],
                    'lastname' => ['type' => 'text'],
                    'my_join_field' => [
                        'type' => 'join',
                        'relations' => [
                            'question' => 'answer',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $mapping->toArray());
        $index->refresh();

        $doc1 = new Document(1, [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ], '_doc');

        $doc2 = new Document(2, [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ], '_doc');

        $index->addDocuments([$doc1, $doc2]);

        $doc3 = new Document(3, [
            'text' => 'this is an answer, the 1st',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ], '_doc');

        $doc4 = new Document(4, [
            'text' => 'this is an answer, the 2nd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc');

        $index->addDocuments([$doc3, $doc4]);
        $index->refresh();

        $results = $index->search([])->getResults();

        $this->assertCount(4, $results);
        foreach ($results as $result) {
            $this->assertArrayHasKey('my_join_field', $result->getData());
        }
    }

    /**
     * @group functional
     */
    public function testMappingExample()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $mapping = new Mapping($type,
            [
                'note' => [
                    'properties' => [
                        'titulo' => ['type' => 'text', 'copy_to' => 'testall', 'boost' => 1.0],
                        'contenido' => ['type' => 'text', 'copy_to' => 'testall', 'boost' => 1.0],
                        'testall' => ['type' => 'text',  'boost' => 1.0],
                    ],
                ],
            ]
        );

        $type->setMapping($mapping);

        $doc = new Document(1, [
                'note' => [
                    [
                        'titulo' => 'nota1',
                        'contenido' => 'contenido1',
                    ],
                    [
                        'titulo' => 'nota2',
                        'contenido' => 'contenido2',
                    ],
                ],
            ]
        );

        $type->addDocument($doc);

        $index->delete();
    }

    /**
     * @group functional
     *
     * Test setting a dynamic template and validate whether the right mapping is applied after adding a document which
     * should match the dynamic template.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic-templates.html
     */
    public function testDynamicTemplate()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $mapping = new Mapping($type);
        $mapping->setParam('dynamic_templates', [
            ['template_1' => [
                'match' => 'multi*',
                'mapping' => [
                    'type' => '{dynamic_type}',
                    'fields' => [
                        'raw' => ['type' => 'keyword'],
                    ],
                ],
            ]],
        ]);

        $mapping->send(['include_type_name' => true]);

        // when running the tests, the mapping sometimes isn't available yet. Force merge index to enforce reload mapping.
        $index->forcemerge();

        // create a document which should create a mapping for the field: multiname.
        $testDoc = new Document('person1', ['multiname' => 'Jasper van Wanrooy'], $type);
        $index->addDocuments([$testDoc]);
        $index->refresh();

        $newMapping = $type->getMapping();
        $this->assertArraySubset(
            [
                '_doc' => [
                    'properties' => [
                        'multiname' => [
                            'type' => 'text',
                            'fields' => [
                                'raw' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $newMapping,
            'Mapping of dynamic "multiname" field should have been created with the type "{dynamic_type}" resolved to "text". '.
            'The "multiname.raw" sub-field should be of type "keyword".'
        );

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testSetMeta()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $mapping = new Mapping($type, [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
        ]);
        $mapping->setMeta(['class' => 'test']);

        $type->setMapping($mapping, ['include_type_name' => true]);

        $mappingData = $type->getMapping();
        $this->assertEquals('test', $mappingData['_doc']['_meta']['class']);

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetters()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $properties = [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
        ];
        $mapping = new Mapping($type, $properties);
        $all = [
           'enabled' => true,
           'store' => true,
        ];
        $mapping->setParam('_all', $all);

        $get_all = $mapping->getParam('_all');

        $this->assertEquals($get_all, $all);

        $this->assertNull($mapping->getParam('_boost', $all));

        $this->assertEquals($properties, $mapping->getProperties());

        $index->delete();
    }
}
