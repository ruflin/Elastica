<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\Builder;
use Elastica\Query\Term;
use Elastica\Query\Text;
use Elastica\Query;
use Elastica\Facet\Terms;
use Elastica\Test\Base as BaseTest;

class QueryTest extends BaseTest
{
    public function testStringConversion()
    {
        $queryString = '{
            "query" : {
                "filtered" : {
                "filter" : {
                    "range" : {
                    "due" : {
                        "gte" : "2011-07-18 00:00:00",
                        "lt" : "2011-07-25 00:00:00"
                    }
                    }
                },
                "query" : {
                    "text_phrase" : {
                    "title" : "Call back request"
                    }
                }
                }
            },
            "sort" : {
                "due" : {
                "reverse" : true
                }
            },
            "fields" : [
                "created", "assigned_to"
            ]
            }';

        $query = new Builder($queryString);
        $queryArray = $query->toArray();

        $this->assertInternalType('array', $queryArray);

        $this->assertEquals('2011-07-18 00:00:00', $queryArray['query']['filtered']['filter']['range']['due']['gte']);
    }

    public function testRawQuery()
    {
        $textQuery = new Term(array('title' => 'test'));

        $query1 = Query::create($textQuery);

        $query2 = new Query();
        $query2->setRawQuery(array('query' => array('term' => array('title' => 'test'))));

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    public function testArrayQuery()
    {
        $query = array(
            'query' => array(
                'text' => array(
                    'title' => 'test'
                )
            )
        );

        $query1 = Query::create($query);

        $query2 = new Query();
        $query2->setRawQuery(array('query' => array('text' => array('title' => 'test'))));

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    public function testSetSort()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'hello world'));
        $type->addDocument($doc);
        $doc = new Document(2, array('firstname' => 'guschti', 'lastname' => 'ruflin'));
        $type->addDocument($doc);
        $doc = new Document(3, array('firstname' => 'nicolas', 'lastname' => 'ruflin'));
        $type->addDocument($doc);

        $queryTerm = new Term();
        $queryTerm->setTerm('lastname', 'ruflin');

        $index->refresh();

        $query = Query::create($queryTerm);

        // ASC order
        $query->setSort(array(array('firstname' => array('order' => 'asc'))));
        $resultSet = $type->search($query);
        $this->assertEquals(2, $resultSet->count());

        $first = $resultSet->current()->getData();
        $second = $resultSet->next()->getData();

        $this->assertEquals('guschti', $first['firstname']);
        $this->assertEquals('nicolas', $second['firstname']);

        // DESC order
        $query->setSort(array('firstname' => array('order' => 'desc')));
        $resultSet = $type->search($query);
        $this->assertEquals(2, $resultSet->count());

        $first = $resultSet->current()->getData();
        $second = $resultSet->next()->getData();

        $this->assertEquals('nicolas', $first['firstname']);
        $this->assertEquals('guschti', $second['firstname']);
    }

    public function testAddSort()
    {
        $query = new Query();
        $sortParam = array('firstname' => array('order' => 'asc'));
        $query->addSort($sortParam);

        $this->assertEquals($query->getParam('sort'), array($sortParam));
    }

    public function testSetRawQuery()
    {
        $query = new Query();

        $params = array('query' => 'test');
        $query->setRawQuery($params);

        $this->assertEquals($params, $query->toArray());
    }

    public function testSetFields()
    {
        $query = new Query();

        $params = array('query' => 'test');

        $query->setFields(array('firstname', 'lastname'));

        $data = $query->toArray();

        $this->assertContains('firstname', $data['fields']);
        $this->assertContains('lastname', $data['fields']);
        $this->assertEquals(2, count($data['fields']));
    }

    public function testGetQuery()
    {
        $query = new Query();

        try {
            $query->getQuery();
            $this->fail('should throw exception because query does not exist');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $this->assertEquals($termQuery->toArray(), $query->getQuery());
    }

    public function testSetFacets()
    {
        $query = new Query();

        $facet = new Terms('text');
        $query->setFacets(array($facet));

        $data = $query->toArray();

        $this->assertArrayHasKey('facets', $data);
        $this->assertEquals(array('text' => array('terms' => array())), $data['facets']);

        $query->setFacets(array());

        $this->assertArrayNotHasKey('facets', $query->toArray());
    }
}
