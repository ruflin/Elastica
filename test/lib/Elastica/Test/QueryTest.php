<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Facet\Terms;
use Elastica\Query;
use Elastica\Query\Builder;
use Elastica\Query\Term;
use Elastica\Query\Text;
use Elastica\Script;
use Elastica\ScriptFields;
use Elastica\Suggest;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class QueryTest extends BaseTest
{
    /**
     * @group unit
     */
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

    /**
     * @group unit
     */
    public function testRawQuery()
    {
        $textQuery = new Term(array('title' => 'test'));

        $query1 = Query::create($textQuery);

        $query2 = new Query();
        $query2->setRawQuery(array('query' => array('term' => array('title' => 'test'))));

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    /**
     * @group unit
     */
    public function testSuggestShouldNotRemoveOtherParameters()
    {
        $query1 = new Query();
        $query2 = new Query();

        $suggest = new Suggest();
        $suggest->setGlobalText('test');

        $query1->setSize(40);
        $query1->setSuggest($suggest);

        $query2->setSuggest($suggest);
        $query2->setSize(40);

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    /**
     * @group unit
     */
    public function testSetSuggestMustReturnQueryInstance()
    {
        $query = new Query();
        $suggest = new Suggest();
        $this->assertInstanceOf('Elastica\Query', $query->setSuggest($suggest));
    }

    /**
     * @group unit
     */
    public function testArrayQuery()
    {
        $query = array(
            'query' => array(
                'text' => array(
                    'title' => 'test',
                ),
            ),
        );

        $query1 = Query::create($query);

        $query2 = new Query();
        $query2->setRawQuery(array('query' => array('text' => array('title' => 'test'))));

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    /**
     * @group functional
     */
    public function testSetSort()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->addDocuments(array(
            new Document(1, array('name' => 'hello world')),
            new Document(2, array('firstname' => 'guschti', 'lastname' => 'ruflin')),
            new Document(3, array('firstname' => 'nicolas', 'lastname' => 'ruflin')),
        ));

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

    /**
     * @group unit
     */
    public function testAddSort()
    {
        $query = new Query();
        $sortParam = array('firstname' => array('order' => 'asc'));
        $query->addSort($sortParam);

        $this->assertEquals($query->getParam('sort'), array($sortParam));
    }

    /**
     * @group unit
     */
    public function testSetRawQuery()
    {
        $query = new Query();

        $params = array('query' => 'test');
        $query->setRawQuery($params);

        $this->assertEquals($params, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetFields()
    {
        $query = new Query();

        $params = array('query' => 'test');

        $query->setFields(array('firstname', 'lastname'));

        $data = $query->toArray();

        $this->assertContains('firstname', $data['fields']);
        $this->assertContains('lastname', $data['fields']);
        $this->assertCount(2, $data['fields']);
    }

    /**
     * @group unit
     */
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

    /**
     * @group unit
     */
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

    /**
     * @group unit
     */
    public function testSetQueryToArrayCast()
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $termQuery->setTerm('text', 'another value');

        $anotherQuery = new Query();
        $anotherQuery->setQuery($termQuery);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetQueryToArrayChangeQuery()
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $queryArray = $query->toArray();

        $termQuery = $query->getQuery();
        $termQuery['term']['text']['value'] = 'another value';

        $this->assertEquals($queryArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetScriptFieldsToArrayCast()
    {
        $query = new Query();
        $scriptFields = new ScriptFields();
        $scriptFields->addScript('script',  new Script('script'));

        $query->setScriptFields($scriptFields);

        $scriptFields->addScript('another script',  new Script('another script'));

        $anotherQuery = new Query();
        $anotherQuery->setScriptFields($scriptFields);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testAddScriptFieldsToArrayCast()
    {
        $query = new Query();
        $scriptField = new Script('script');

        $query->addScriptField('script', $scriptField);

        $scriptField->setScript('another script');

        $anotherQuery = new Query();
        $anotherQuery->addScriptField('script', $scriptField);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testAddFacetToArrayCast()
    {
        $query = new Query();
        $facet = new Terms('text');

        $query->addFacet($facet);

        $facet->setName('another text');

        $anotherQuery = new Query();
        $anotherQuery->addFacet($facet);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testAddAggregationToArrayCast()
    {
        $query = new Query();
        $aggregation = new \Elastica\Aggregation\Terms('text');

        $query->addAggregation($aggregation);

        $aggregation->setName('another text');

        $anotherQuery = new Query();
        $anotherQuery->addAggregation($aggregation);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetSuggestToArrayCast()
    {
        $query = new Query();
        $suggest = new Suggest();
        $suggest->setGlobalText('text');

        $query->setSuggest($suggest);

        $suggest->setGlobalText('another text');

        $anotherQuery = new Query();
        $anotherQuery->setSuggest($suggest);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetRescoreToArrayCast()
    {
        $query = new Query();
        $rescore = new \Elastica\Rescore\Query();
        $rescore->setQueryWeight(1);

        $query->setRescore($rescore);

        $rescore->setQueryWeight(2);

        $anotherQuery = new Query();
        $anotherQuery->setRescore($rescore);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetPostFilterToArrayCast()
    {
        $query = new Query();
        $postFilter = new \Elastica\Filter\Terms();
        $postFilter->setTerms('key', array('term'));
        $query->setPostFilter($postFilter);

        $postFilter->setTerms('another key', array('another term'));

        $anotherQuery = new Query();
        $anotherQuery->setPostFilter($postFilter);

        $this->assertNotEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group functional
     */
    public function testNoSource()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            array('username' => 'ruflin', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc1);

        // To update index
        $index->refresh();

        $query = Query::create('ruflin');
        $resultSet = $type->search($query);

        // Disable source
        $query->setSource(false);

        $resultSetNoSource = $type->search($query);

        $this->assertEquals(1, $resultSet->count());
        $this->assertEquals(1, $resultSetNoSource->count());

        // Tests if no source is in response except id
        $result = $resultSetNoSource->current();
        $this->assertEquals(1, $result->getId());
        $this->assertEmpty($result->getData());

        // Tests if source is in response except id
        $result = $resultSet->current();
        $this->assertEquals(1, $result->getId());
        $this->assertNotEmpty($result->getData());
    }
}
