<?php

namespace Elastica\Test\Type;

use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class MappingTest extends BaseTest
{
    public function testMappingStoreFields()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create(array(), true);
        $type = $index->getType('test');

        $mapping = new Mapping($type,
            array(
                'firstname' => array('type' => 'string', 'store' => 'yes'),
                // default is store => no expected
                'lastname' => array('type' => 'string'),
            )
        );
        $mapping->disableSource();

        $type->setMapping($mapping);

        $firstname = 'Nicolas';
        $doc = new Document(1,
            array(
                'firstname' => $firstname,
                'lastname' => 'Ruflin'
            )
        );

        $type->addDocument($doc);

        $index->refresh();
        $queryString = new QueryString('ruflin');
        $query = Query::create($queryString);
        $query->setFields(array('*'));

        $resultSet = $type->search($query);
        $result = $resultSet->current();
        $fields = $result->getFields();

        $this->assertEquals($firstname, $fields['firstname']);
        $this->assertArrayNotHasKey('lastname', $fields);
        $this->assertEquals(1, count($fields));

        $index->flush();
        $document = $type->getDocument(1);

        $this->assertEmpty($document->getData());
    }

    public function testEnableTtl()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create(array(), true);
        $type = $index->getType('test');

        $mapping = new Mapping($type, array());

        $mapping->enableTtl();

        $data = $mapping->toArray();
        $this->assertTrue($data[$type->getName()]['_ttl']['enabled']);
    }

    public function testNestedMapping()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create(array(), true);
        $type = $index->getType('test');

        $this->markTestIncomplete('nested mapping is not set right yet');
        $mapping = new Mapping($type,
            array(
                'test' => array(
                    'type' => 'object', 'store' => 'yes', 'properties' => array(
                        'user' => array(
                            'properties' => array(
                                'firstname' => array('type' => 'string', 'store' => 'yes'),
                                'lastname' => array('type' => 'string', 'store' => 'yes'),
                                'age' => array('type' => 'integer', 'store' => 'yes'),
                            )
                        ),
                    ),
                ),
            )
        );

        $type->setMapping($mapping);

        $doc = new Document(1, array(
            'user' => array(
                'firstname' => 'Nicolas',
                'lastname' => 'Ruflin',
                'age' => 9
            ),
        ));

        //print_r($type->getMapping());
        //exit();
        $type->addDocument($doc);

        $index->refresh();
        $resultSet = $type->search('ruflin');
        //print_r($resultSet);
    }

    public function testParentMapping()
    {
        $index = $this->_createIndex();
        $parenttype = new Type($index, 'parenttype');
        $parentmapping = new Mapping($parenttype,
            array(
                'name' => array('type' => 'string', 'store' => 'yes')
            )
        );

        $parenttype->setMapping($parentmapping);

        $childtype = new Type($index, 'childtype');
        $childmapping = new Mapping($childtype,
            array(
                'name' => array('type' => 'string', 'store' => 'yes'),
            )
        );
        $childmapping->setParam('_parent', array('type' => 'parenttype'));

        $childtype->setMapping($childmapping);
    }

    public function testMappingExample()
    {
        $index = $this->_createIndex();
        $type = $index->getType('notes');

        $mapping = new Mapping($type,
            array(
                'note' => array(
                    'store' => 'yes', 'properties' => array(
                        'titulo'  => array('type' => 'string', 'store' => 'no', 'include_in_all' => true, 'boost' => 1.0),
                        'contenido' => array('type' => 'string', 'store' => 'no', 'include_in_all' => true, 'boost' => 1.0)
                    )
                )
            )
        );

        $type->setMapping($mapping);

        $doc = new Document(1, array(
                'note' => array(
                    array(
                        'titulo'        => 'nota1',
                        'contenido'        => 'contenido1'
                    ),
                    array(
                        'titulo'        => 'nota2',
                        'contenido'        => 'contenido2'
                    )
                )
            )
        );

        $type->addDocument($doc);
    }
}
