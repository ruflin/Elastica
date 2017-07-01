<?php
namespace Elastica\Test\Type;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
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
        $type = $index->getType('test');

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
        $this->assertEquals(1, count($fields));

        $index->flush();
        $document = $type->getDocument(1);

        $this->assertEmpty($document->getData());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testEnableAllField()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $mapping = new Mapping($type, []);

        $mapping->enableAllField();

        $data = $mapping->toArray();
        $this->assertTrue($data[$type->getName()]['_all']['enabled']);

        $response = $mapping->send();
        $this->assertTrue($response->isOk());

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
        $type = $index->getType('test');

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
    public function testParentMapping()
    {
        $index = $this->_createIndex();

        $childtype = new Type($index, 'childtype');
        $childmapping = new Mapping($childtype,
            [
                'name' => ['type' => 'text', 'store' => true],
            ]
        );
        $childmapping->setParent('parenttype');

        $childtype->setMapping($childmapping);

        $data = $childmapping->toArray();
        $this->assertEquals('parenttype', $data[$childtype->getName()]['_parent']['type']);

        $parenttype = new Type($index, 'parenttype');
        $parentmapping = new Mapping($parenttype,
            [
                'name' => ['type' => 'text', 'store' => true],
            ]
        );

        $parenttype->setMapping($parentmapping);
    }

    /**
     * @group functional
     */
    public function testMappingExample()
    {
        $index = $this->_createIndex();
        $type = $index->getType('notes');

        $mapping = new Mapping($type,
            [
                'note' => [
                    'properties' => [
                        'titulo' => ['type' => 'text', 'include_in_all' => true, 'boost' => 1.0],
                        'contenido' => ['type' => 'text', 'include_in_all' => true, 'boost' => 1.0],
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
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic-templates.html
     */
    public function testDynamicTemplate()
    {
        $index = $this->_createIndex();
        $type = $index->getType('person');

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

        $mapping->send();

        // when running the tests, the mapping sometimes isn't available yet. Force merge index to enforce reload mapping.
        $index->forcemerge();

        // create a document which should create a mapping for the field: multiname.
        $testDoc = new Document('person1', ['multiname' => 'Jasper van Wanrooy'], $type);
        $index->addDocuments([$testDoc]);
        $index->refresh();

        $newMapping = $type->getMapping();
        $this->assertArraySubset(
            [
                'person' => [
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
        $type = $index->getType('test');
        $mapping = new Mapping($type, [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
        ]);
        $mapping->setMeta(['class' => 'test']);
        $type->setMapping($mapping);

        $mappingData = $type->getMapping();
        $this->assertEquals('test', $mappingData['test']['_meta']['class']);

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetters()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
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
