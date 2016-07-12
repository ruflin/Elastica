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
                'firstname' => ['type' => 'string', 'store' => true],
                // default is store => no expected
                'lastname' => ['type' => 'string'],
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
        $query->setFields(['*']);

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
    public function testEnableTtl()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create([], true);
        $type = $index->getType('test');

        $mapping = new Mapping($type, []);

        $mapping->enableTtl();

        $data = $mapping->toArray();
        $this->assertTrue($data[$type->getName()]['_ttl']['enabled']);

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
                                'firstname' => ['type' => 'string', 'store' => true],
                                'lastname' => ['type' => 'string', 'store' => true],
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
                'name' => ['type' => 'string', 'store' => true],
            ]
        );
        $childmapping->setParent('parenttype');

        $childtype->setMapping($childmapping);

        $data = $childmapping->toArray();
        $this->assertEquals('parenttype', $data[$childtype->getName()]['_parent']['type']);

        $parenttype = new Type($index, 'parenttype');
        $parentmapping = new Mapping($parenttype,
            [
                'name' => ['type' => 'string', 'store' => true],
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
                        'titulo' => ['type' => 'string', 'store' => 'no', 'include_in_all' => true, 'boost' => 1.0],
                        'contenido' => ['type' => 'string', 'store' => 'no', 'include_in_all' => true, 'boost' => 1.0],
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
     * should match the dynamic template. The example is the template_1 from the Elasticsearch documentation.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-root-object-type.html
     */
    public function testDynamicTemplate()
    {
        $index = $this->_createIndex();
        $type = $index->getType('person');

        // set a dynamic template "template_1" which creates a multi field for multi* matches.
        $mapping = new Mapping($type);
        $mapping->setParam('dynamic_templates', [
            ['template_1' => [
                'match' => 'multi*',
                'mapping' => [
                    'type' => 'multi_field',
                    'fields' => [
                        '{name}' => ['type' => '{dynamic_type}', 'index' => 'analyzed'],
                        'org' => ['type' => '{dynamic_type}', 'index' => 'not_analyzed'],
                    ],
                ],
            ]],
        ]);

        $mapping->send();

        // when running the tests, the mapping sometimes isn't available yet. Optimize index to enforce reload mapping.
        $index->optimize();

        // create a document which should create a mapping for the field: multiname.
        $testDoc = new Document('person1', ['multiname' => 'Jasper van Wanrooy'], $type);
        $index->addDocuments([$testDoc]);
        sleep(1);   //sleep 1 to ensure that the test passes every time

        // read the mapping from Elasticsearch and assert that the multiname.org field is "not_analyzed"
        $newMapping = $type->getMapping();
        $this->assertArrayHasKey('person', $newMapping,
            'Person type not available in mapping from ES. Mapping set at all?');
        $this->assertArrayHasKey('properties', $newMapping['person'],
            'Person type doesnt have any properties. Document properly added?');
        $this->assertArrayHasKey('multiname', $newMapping['person']['properties'],
            'The multiname property is not added to the mapping. Document properly added?');
        $this->assertArrayHasKey('fields', $newMapping['person']['properties']['multiname'],
            'The multiname field of the Person type is presumably not a multi_field type. Dynamic mapping not applied?');
        $this->assertArrayHasKey('org', $newMapping['person']['properties']['multiname']['fields'],
            'The multi* matcher did not create a mapping for the multiname.org property when indexing the document.');
        $this->assertArrayHasKey('index', $newMapping['person']['properties']['multiname']['fields']['org'],
            'Indexing status of the multiname.org not available. Dynamic mapping not fully applied!');
        $this->assertEquals('not_analyzed', $newMapping['person']['properties']['multiname']['fields']['org']['index']);

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
            'firstname' => ['type' => 'string', 'store' => true],
            'lastname' => ['type' => 'string'],
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
            'firstname' => ['type' => 'string', 'store' => true],
            'lastname' => ['type' => 'string'],
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
