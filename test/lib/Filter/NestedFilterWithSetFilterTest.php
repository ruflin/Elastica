<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\Nested;
use Elastica\Filter\Terms;
use Elastica\Search;
use Elastica\Type\Mapping;
use Elastica\Test\Base as BaseTest;

class NestedFilterWithSetFilterTest extends BaseTest
{
    public function setUp()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test_filter_nested_abstract_filter');
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
                    'properties' => array('hobby' => array('type' => 'string'))
                )
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
                    array('hobby' => 'opensource')
                )
            )
        );
        $docs[] = new Document(2,
            array(
                'firstname' => 'Nicolas',
                'lastname' => 'Ippolito',
                'hobbies' => array(
                    array('hobby' => 'opensource'),
                    array('hobby' => 'guitar'),
                )
            )
        );
        $response = $type->addDocuments($docs);

        // Refresh index
        $index->refresh();
    }

    public function tearDown()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test_filter_nested_abstract_filter');
        $index->delete();
    }

    public function testToArray()
    {
        $f = new Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Terms();
        $q->setTerms('hobby', array('guitar'));
        $f->setPath('hobbies');
        $f->setFilter($q);

        $expectedArray = array(
            'nested' => array(
                'path' => 'hobbies',
                'filter' => array('terms' => array(
                    'hobby' => array('guitar')
                ))
            )
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
        $f->setFilter($q);

        $c = $this->_getClient();
        $s = new Search($c);
        $i = $c->getIndex('elastica_test_filter_nested_abstract_filter');
        $s->addIndex($i);
        $r = $s->search($f);

        $this->assertEquals(1, $r->getTotalHits());

        $f = new Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Terms();
        $q->setTerms('hobby', array('opensource'));
        $f->setPath('hobbies');
        $f->setFilter($q);

        $c = $this->_getClient();
        $s = new Search($c);
        $i = $c->getIndex('elastica_test_filter_nested_abstract_filter');
        $s->addIndex($i);
        $r = $s->search($f);
        $this->assertEquals(2, $r->getTotalHits());
    }
}
