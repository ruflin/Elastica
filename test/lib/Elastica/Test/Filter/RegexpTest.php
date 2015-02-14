<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\Regexp;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class RegexpTest extends BaseTest
{
    public function testToArray()
    {
        $field = 'name';
        $regexp = 'ruf';

        $filter = new Regexp($field, $regexp);

        $expectedArray = array(
            'regexp' => array(
                $field => $regexp,
            ),
        );

        $this->assertequals($expectedArray, $filter->toArray());
    }
    
    public function testToArrayWithOptions()
    {
        $field = 'name';
        $regexp = 'ruf';
        $options = array(
            'flags' => 'ALL'
        );
        
        $filter = new Regexp($field, $regexp, $options);
        
        $expectedArray = array(
            'regexp' => array(
                $field => array(
                    'value' => $regexp,
                    'flags' => 'ALL'
                )
            )
        );
        
        $this->assertequals($expectedArray, $filter->toArray());
    }

    public function testDifferentRegexp()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create(array(), true);
        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
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

        $query = new Regexp('name', 'Ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // Lower case should not return a result
        $query = new Regexp('name', 'ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());

        $query = new Regexp('name', 'Baden.*');
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $query = new Regexp('name', 'Baden B.*');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        $query = new Regexp('name', 'Baden Bas.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
    }

    public function testDifferentRegexpLowercase()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $indexParams = array(
            'analysis' => array(
                'analyzer' => array(
                    'lw' => array(
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array('lowercase'),
                    ),
                ),
            ),
        );

        $index->create($indexParams, true);
        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
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

        $query = new Regexp('name', 'ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // Upper case should not return a result
        $query = new Regexp('name', 'Ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());

        $query = new Regexp('name', 'baden.*');
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $query = new Regexp('name', 'baden b.*');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        $query = new Regexp('name', 'baden bas.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
    }
}
