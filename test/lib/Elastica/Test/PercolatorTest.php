<?php

namespace Elastica\Test;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Percolator;
use Elastica\Query\Term;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class PercolatorTest extends BaseTest
{
    public function testConstruct()
    {
        $percolatorName = 'percotest';

        $index = $this->_createIndex($percolatorName);
        $percolator = new Percolator($index);

        $query = new Term(array('field1' => 'value1'));
        $response = $percolator->registerQuery($percolatorName, $query);

        $data = $response->getData();

        $expectedArray = array(
            'ok' => true,
            '_type' => $index->getName(),
            '_index' => '_percolator',
            '_id' => $percolatorName,
            '_version' => 1
        );

        $this->assertEquals($expectedArray, $data);
    }

    public function testMatchDoc()
    {
        $client = new Client(array('persistent' => false));
        $index = $client->getIndex('elastica_test');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $percolator = new Percolator($index);

        $percolatorName = 'percotest';
        $percolatorSecondName = 'percotest_color';

        $query = new Term(array('name' => 'ruflin'));
        $response = $percolator->registerQuery($percolatorName, $query);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        // Add index with the same query and additional parameter
        $secondParamKey = 'color';
        $secondParamValue = 'blue';
        $querySecond = new Query();
        $querySecond->setQuery($query);
        $querySecond->setParam($secondParamKey, $secondParamValue);
        $responseSecond = $percolator->registerQuery($percolatorSecondName, $querySecond);

        // Check if it's ok
        $this->assertTrue($responseSecond->isOk());
        $this->assertFalse($responseSecond->hasError());

        $doc1 = new Document();
        $doc1->set('name', 'ruflin');

        $doc2 = new Document();
        $doc2->set('name', 'nicolas');

        $index = new Index($index->getClient(), '_percolator');
        $index->optimize();
        $index->refresh();

        $matches1 = $percolator->matchDoc($doc1);

        $this->assertTrue(in_array($percolatorName, $matches1));
        $this->assertTrue(in_array($percolatorSecondName, $matches1));
        $this->assertEquals(2, count($matches1));

        $matches2 = $percolator->matchDoc($doc2);
        $this->assertEmpty($matches2);

        // Test using document with additional parameter
        $docSecond = $doc1;
        $docSecond->set($secondParamKey, $secondParamValue);

        // Create term for our parameter to filter data while percolating
        $secondTerm = new Term(array($secondParamKey => $secondParamValue));
        $secondQuery = new Query();
        $secondQuery->setQuery($secondTerm);

        // Match the document to percolator queries and filter results using optional parameter
        $secondMatches = $percolator->matchDoc($docSecond, $secondQuery);

        // Check if only one proper index was returned
        $this->assertFalse(in_array($percolatorName, $secondMatches));
        $this->assertTrue(in_array($percolatorSecondName, $secondMatches));
        $this->assertEquals(1, count($secondMatches));
    }

    /**
     * Test case for using filtered percolator queries based on the Elasticsearch documentation examples.
     */
    public function testFilteredMatchDoc()
    {
        // step one: register create index and setup the percolator query from the ES documentation.
        $index = $this->_createIndex();
        $percolator = new Percolator($index);
        $baseQuery = new Term(array('field1' => 'value1'));
        $fields = array('color' => 'blue');
        
        $response = $percolator->registerQuery('kuku', $baseQuery, $fields);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        // refreshing is required in order to ensure the query is really ready for execution.
        $index = new Index($index->getClient(), '_percolator');
        $index->refresh();
        
        // step two: match a document which should match the kuku query when filtered on the blue color
        $doc = new Document();
        $doc->set('field1', 'value1');
        
        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'blue')));
        $this->assertCount(1, $matches, 'No or too much registered query matched.');
        $this->assertEquals('kuku', $matches[0], 'A wrong registered query has matched.');
        
        // step three: validate that using a different color, no registered query matches.
        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'green')));
        $this->assertCount(0, $matches, 'A registered query matched, although nothing should match at all.');
    }
}
