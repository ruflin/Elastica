<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Field;
use Elastica\Test\Base as BaseTest;

class FieldTest extends BaseTest
{
    public function testTextPhrase()
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

        $query = new Field();
        $query->setField('name');
        $query->setQueryString('"Baden Baden"');

        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    public function testToArray()
    {
        $query = new Field('user', 'jack');
        $expected = array('field' => array('user' => array('query' => 'jack')));

        $this->assertSame($expected, $query->toArray());
    }
}
