<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class MappingTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testMappingStoreFields(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create([], true);

        $mapping = new Mapping([
            'firstname' => ['type' => 'text', 'store' => true],
            // default is store => no expected
            'lastname' => ['type' => 'text'],
        ]);
        $mapping->disableSource();

        $index->setMapping($mapping);

        $firstname = 'Nicolas';
        $doc = new Document(
            1,
            [
                'firstname' => $firstname,
                'lastname' => 'Ruflin',
            ]
        );

        $index->addDocuments([$doc]);

        $index->refresh();
        $queryString = new QueryString('ruflin');
        $query = Query::create($queryString);
        $query->setStoredFields(['*']);

        $resultSet = $index->search($query);
        $result = $resultSet->current();
        $fields = $result->getFields();

        $this->assertEquals($firstname, $fields['firstname'][0]);
        $this->assertArrayNotHasKey('lastname', $fields);
        $this->assertCount(1, $fields);

        $index->flush();
        $document = $index->getDocument(1);

        $this->assertEmpty($document->getData());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testNestedMapping(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create([], true);

        $mapping = new Mapping([
            'user' => [
                'properties' => [
                    'firstname' => ['type' => 'text', 'store' => true],
                    'lastname' => ['type' => 'text', 'store' => true],
                    'age' => ['type' => 'integer', 'store' => true],
                ],
            ],
        ]);

        $response = $index->setMapping($mapping);
        $this->assertFalse($response->hasError());

        $doc = new Document(1, [
            'user' => [
                'firstname' => 'Nicolas',
                'lastname' => 'Ruflin',
                'age' => 9,
            ],
        ]);

        $index->addDocuments([$doc]);

        $index->refresh();
        $resultSet = $index->search('ruflin');
        $this->assertEquals($resultSet->count(), 1);

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testJoinMapping(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testjoinparentid');
        $index->create([], true);

        $mapping = new Mapping([
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
        ];

        $this->assertEquals($expected, $mapping->toArray());
        $index->refresh();

        $doc1 = new Document(1, [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);

        $doc2 = new Document(2, [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);

        $index->addDocuments([$doc1, $doc2]);

        $doc3 = new Document(3, [
            'text' => 'this is an answer, the 1st',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);

        $doc4 = new Document(4, [
            'text' => 'this is an answer, the 2nd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);

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
    public function testMappingExample(): void
    {
        $index = $this->_createIndex();

        $mapping = new Mapping([
            'note' => [
                'properties' => [
                    'titulo' => ['type' => 'text', 'copy_to' => 'testall', 'boost' => 1.0],
                    'contenido' => ['type' => 'text', 'copy_to' => 'testall', 'boost' => 1.0],
                    'testall' => ['type' => 'text',  'boost' => 1.0],
                ],
            ],
        ]);

        $index->setMapping($mapping);

        $doc = new Document(
            1,
            [
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

        $index->addDocuments([$doc]);

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
    public function testDynamicTemplate(): void
    {
        $index = $this->_createIndex();
        $rawFieldsType = ['raw' => ['type' => 'keyword']];

        $mapping = new Mapping();
        $mapping->setParam('dynamic_templates', [
            ['template_1' => [
                'match' => 'multi*',
                'mapping' => [
                    'type' => '{dynamic_type}',
                    'fields' => $rawFieldsType,
                ],
            ]],
        ]);

        $index->setMapping($mapping);

        // when running the tests, the mapping sometimes isn't available yet. Force merge index to enforce reload mapping.
        $index->forcemerge();

        // create a document which should create a mapping for the field: multiname.
        $testDoc = new Document('person1', ['multiname' => 'Jasper van Wanrooy']);
        $index->addDocuments([$testDoc]);
        $index->refresh();

        $newMapping = $index->getMapping();
        $this->assertArrayHasKey('properties', $newMapping);
        $this->assertArrayHasKey('multiname', $newMapping['properties']);
        $this->assertSame(
            [
                'type' => 'text',
                'fields' => $rawFieldsType,
            ],
            $newMapping['properties']['multiname'],
            'Mapping of dynamic "multiname" field should have been created with the type "{dynamic_type}" resolved to "text". '.
            'The "multiname.raw" sub-field should be of type "keyword".'
        );

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testSetMeta(): void
    {
        $index = $this->_createIndex();
        $mapping = new Mapping([
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
        ]);
        $mapping->setMeta(['class' => 'test']);

        $index->setMapping($mapping);

        $mappingData = $index->getMapping();
        $this->assertEquals('test', $mappingData['_meta']['class'], \json_encode($mappingData));

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetters(): void
    {
        $properties = [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
        ];
        $mapping = new Mapping($properties);
        $all = [
            'enabled' => true,
            'store' => true,
        ];
        $mapping->setParam('_all', $all);

        $getAll = $mapping->getParam('_all');

        $this->assertEquals($getAll, $all);
        $this->assertNull($mapping->getParam('_boost'));
        $this->assertEquals($properties, $mapping->getProperties());
    }
}
