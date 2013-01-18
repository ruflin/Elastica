<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Text;
use Elastica\Test\Base as BaseTest;

class TextTest extends BaseTest
{
    public function testToArray()
    {
        $queryText = 'Nicolas Ruflin';
        $type = 'text_phrase';
        $analyzer = 'myanalyzer';
        $maxExpansions = 12;
        $field = 'test';

        $query = new Text();
        $query->setFieldQuery($field, $queryText);
        $query->setFieldType($field, $type);
        $query->setFieldParam($field, 'analyzer', $analyzer);
        $query->setFieldMaxExpansions($field, $maxExpansions);

        $expectedArray = array(
            'text' => array(
                $field => array(
                    'query' => $queryText,
                    'type' => $type,
                    'analyzer' => $analyzer,
                    'max_expansions' => $maxExpansions,
                )
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

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
        $doc = new Document(3, array('name' => 'New Hampshire'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Basel Land'));
        $type->addDocument($doc);

        $index->refresh();

        $type = 'text_phrase';
        $field = 'name';

        $query = new Text();
        $query->setFieldQuery($field, 'Basel New');
        $query->setField('operator', 'OR');
        $query->setFieldType($field, $type);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }
}
