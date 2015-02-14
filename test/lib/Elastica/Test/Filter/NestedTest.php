<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\Nested;
use Elastica\Query\Terms;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class NestedTest extends BaseTest
{
    public function setUp()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test_filter_nested');
        $index->create(array(), true);
        $type = $index->getType('user');
        $mapping = new Mapping();
        $mapping->setProperties(
            array(
                'firstname' => array('type' => 'string', 'store' => 'yes'),
                // default is store => no expected
                'lastname' => array('type' => 'string'),
                'hobbies' => array(
                    'type' => 'nested',
                    'include_in_parent' => true,
                    'properties' => array('hobby' => array('type' => 'string')),
                ),
            )
        );
        $type->setMapping($mapping);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Document(1,
            array(
                'firstname' => 'Nicolas',
                'lastname' => 'Ruflin',
                'hobbies' => array(
                    array('hobby' => 'opensource'),
                ),
            )
        );
        $docs[] = new Document(2,
            array(
                'firstname' => 'Nicolas',
                'lastname' => 'Ippolito',
                'hobbies' => array(
                    array('hobby' => 'opensource'),
                    array('hobby' => 'guitar'),
                ),
            )
        );
        $response = $type->addDocuments($docs);

        // Refresh index
        $index->refresh();
    }

    public function testToArray()
    {
        $f = new Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Terms();
        $q->setTerms('hobby', array('guitar'));
        $f->setPath('hobbies');
        $f->setQuery($q);

        $expectedArray = array(
            'nested' => array(
                'path' => 'hobbies',
                'query' => array('terms' => array(
                    'hobby' => array('guitar'),
                )),
            ),
        );

        $this->assertEquals($expectedArray, $f->toArray());
    }

    public function testShouldReturnTheRightNumberOfResult()
    {
        $f = new Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Terms();
        $q->setTerms('hobby', array('guitar'));
        $f->setPath('hobbies');
        $f->setQuery($q);

        $c = $this->_getClient();
        $s = new Search($c);
        $i = $c->getIndex('elastica_test_filter_nested');
        $s->addIndex($i);
        $r = $s->search($f);

        $this->assertEquals(1, $r->getTotalHits());

        $f = new Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Terms();
        $q->setTerms('hobby', array('opensource'));
        $f->setPath('hobbies');
        $f->setQuery($q);

        $c = $this->_getClient();
        $s = new Search($c);
        $i = $c->getIndex('elastica_test_filter_nested');
        $s->addIndex($i);
        $r = $s->search($f);
        $this->assertEquals(2, $r->getTotalHits());
    }

    public function testSetJoin()
    {
        $filter = new Nested();

        $this->assertTrue($filter->setJoin(true)->getParam('join'));

        $this->assertFalse($filter->setJoin(false)->getParam('join'));

        $returnValue = $filter->setJoin(true);
        $this->assertInstanceOf('Elastica\Filter\Nested', $returnValue);
    }
}
