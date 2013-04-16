<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Fuzzy;
use Elastica\Test\Base as BaseTest;

class FuzzyTest extends BaseTest
{
    public function testToArray()
    {
        $fuzzy = new Fuzzy();
        $fuzzy->addField('user', array('value' => 'Nicolas', 'boost' => 1.0));
        $expectedArray = array(
            'fuzzy' => array(
                'user' => array(
                    'value' => 'Nicolas',
                    'boost' => 1.0
                )
            )
        );
        $this->assertEquals($expectedArray, $fuzzy->toArray(), 'Deprecated method failed');

        $fuzzy = new Fuzzy('user', 'Nicolas');
        $expectedArray = array(
            'fuzzy' => array(
                'user' => array(
                    'value' => 'Nicolas',
                )
            )
        );
        $this->assertEquals($expectedArray, $fuzzy->toArray());

        $fuzzy = new Fuzzy();
        $fuzzy->setField('user', 'Nicolas')->setFieldOption('boost', 1.0);
        $expectedArray = array(
            'fuzzy' => array(
                'user' => array(
                    'value' => 'Nicolas',
                    'boost' => 1.0
                )
            )
        );
        $this->assertEquals($expectedArray, $fuzzy->toArray());
    }

    public function testQuery()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'Baden'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Baden Baden'));
        $type->addDocument($doc);

        $index->refresh();

        $field = 'name';

        $query = new Fuzzy();
        $query->setField($field, 'Baden');

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    public function testBadArguments ()
    {
        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->addField('name', array(array('value' => 'Baden')));

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', array());

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', 'value');
        $query->setField('name1', 'value1');
    }
}
