<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_NestedTest extends Elastica_Test
{
    public function setUp() {
        $client = new Elastica_Client();
        $index = $client->getIndex('elastica_test_filter_nested');
		$index->create(array(), true);
        $type = $index->getType('user');
        $mapping = new Elastica_Type_Mapping();
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
		$docs[] = new Elastica_Document(1,
            array(
                'firstname' => 'Nicolas',
                'lastname' => 'Ruflin',
                'hobbies' => array(
                    array('hobby' => 'opensource')
                )
            )
		);
		$docs[] = new Elastica_Document(2,
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

    public function tearDown() {
        $client = new Elastica_Client();
        $index = $client->getIndex('elastica_test_filter_nested');
        $index->delete();
	}

	public function testToArray() {
        $f = new Elastica_Filter_Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Elastica_Query_Terms();
        $q->setTerms('hobby', array('guitar'));
        $f->setPath('hobbies');
        $f->setQuery($q);

        $expectedArray = array(
            'nested' => array(
                'path' => 'hobbies',
                'query' => array('terms' => array(
                    'hobby' => array('guitar')
                ))
            )
        );

        $this->assertEquals($expectedArray, $f->toArray());
    }

    public function testShouldReturnTheRightNumberOfResult()
    {
        $f = new Elastica_Filter_Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Elastica_Query_Terms();
        $q->setTerms('hobby', array('guitar'));
        $f->setPath('hobbies');
        $f->setQuery($q);

        $c = new Elastica_Client();
        $s = new Elastica_Search($c);
        $i = $c->getIndex('elastica_test_filter_nested');
        $s->addIndex($i);
        $r = $s->search($f);

        $this->assertEquals(1, $r->getTotalHits());

        $f = new Elastica_Filter_Nested();
        $this->assertEquals(array('nested' => array()), $f->toArray());
        $q = new Elastica_Query_Terms();
        $q->setTerms('hobby', array('opensource'));
        $f->setPath('hobbies');
        $f->setQuery($q);

        $c = new Elastica_Client();
        $s = new Elastica_Search($c);
        $i = $c->getIndex('elastica_test_filter_nested');
        $s->addIndex($i);
        $r = $s->search($f);
        $this->assertEquals(2, $r->getTotalHits());
    }
}
