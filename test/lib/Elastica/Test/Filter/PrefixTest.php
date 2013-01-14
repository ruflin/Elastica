<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\PrefixFilter;
use Elastica\Type\MappingType;
use Elastica\Test\Base as BaseTest;

class PrefixTest extends BaseTest
{
    public function testToArray()
    {
        $field = 'name';
        $prefix = 'ruf';

        $filter = new PrefixFilter($field, $prefix);

        $expectedArray = array(
            'prefix' => array(
                $field => $prefix
            )
        );

        $this->assertequals($expectedArray, $filter->toArray());
    }

    public function testDifferentPrefixes()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        /*$indexParams = array(
            'analysis' => array(
                'analyzer' => array(
                    'lw' => array(
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array('lowercase')
                    )
                ),
            )
        );*/

        $index->create(array(), true);
        $type = $index->getType('test');

        $mapping = new MappingType($type, array(
                'name' => array('type' => 'string', 'store' => 'no', 'index' => 'not_analyzed'),
            )
        );
        $type->setMapping($mapping);

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'Baden'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Baden Baden'));
        $type->addDocument($doc);
        $doc = new Document(5, array('name' => 'New Orleans'));
        $type->addDocument($doc);

        $index->refresh();

        $query = new PrefixFilter('name', 'Ba');
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // Lower case should not return a result
        $query = new PrefixFilter('name', 'ba');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());

        $query = new PrefixFilter('name', 'Baden');
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $query = new PrefixFilter('name', 'Baden B');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        $query = new PrefixFilter('name', 'Baden Bas');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
    }

    public function testDifferentPrefixesLowercase()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $indexParams = array(
            'analysis' => array(
                'analyzer' => array(
                    'lw' => array(
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array('lowercase')
                    )
                ),
            )
        );

        $index->create($indexParams, true);
        $type = $index->getType('test');

        $mapping = new MappingType($type, array(
                'name' => array('type' => 'string', 'store' => 'no', 'analyzer' => 'lw'),
            )
        );
        $type->setMapping($mapping);

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'Baden'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Baden Baden'));
        $type->addDocument($doc);
        $doc = new Document(5, array('name' => 'New Orleans'));
        $type->addDocument($doc);

        $index->refresh();

        $query = new PrefixFilter('name', 'ba');
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // Upper case should not return a result
        $query = new PrefixFilter('name', 'Ba');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());

        $query = new PrefixFilter('name', 'baden');
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $query = new PrefixFilter('name', 'baden b');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        $query = new PrefixFilter('name', 'baden bas');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
    }
}
