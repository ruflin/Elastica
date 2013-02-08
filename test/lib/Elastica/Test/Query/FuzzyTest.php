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

        $type = 'text_phrase';
        $field = 'name';

        $query = new Fuzzy();
        $query->addField('name', array('value' => 'Baden'));

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }
}
