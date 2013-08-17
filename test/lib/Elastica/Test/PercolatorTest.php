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
        $index = $this->_createIndex();
        $percolator = new Percolator($index);

        $percolatorName = 'percotest';

        $query = new Term(array('name' => 'ruflin'));
        $response = $percolator->registerQuery($percolatorName, $query);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $doc1 = new Document();
        $doc1->set('name', 'ruflin');

        $doc2 = new Document();
        $doc2->set('name', 'nicolas');

        $index = new Index($index->getClient(), '_percolator');
        $index->optimize();
        $index->refresh();

        $matches1 = $percolator->matchDoc($doc1);

        $this->assertTrue(in_array($percolatorName, $matches1));
        $this->assertCount(1, $matches1);

        $matches2 = $percolator->matchDoc($doc2);
        $this->assertEmpty($matches2);
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
        $percolatorIndex = new Index($index->getClient(), '_percolator');
        $percolatorIndex->refresh();
        $percolatorIndex->optimize();
        
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
