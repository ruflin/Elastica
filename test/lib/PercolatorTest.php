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
            '_type' => '.percolator',
            '_index' => $index->getName(),
            '_id' => $percolatorName,
            '_version' => 1,
            'created' => 1
        );

        $this->assertEquals($expectedArray, $data);

        $index->delete();
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

        $index->refresh();

        $matches1 = $percolator->matchDoc($doc1);

        $this->assertCount(1, $matches1);
        $firstPercolatorFound = false;
        foreach ($matches1 as $match) {
            if ($match['_id'] == $percolatorName) {
                $firstPercolatorFound = true;
            }
        }
        $this->assertTrue($firstPercolatorFound);

        $matches2 = $percolator->matchDoc($doc2);
        $this->assertEmpty($matches2);

        $index->delete();
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
        $index->refresh();

        // step two: match a document which should match the kuku query when filtered on the blue color
        $doc = new Document();
        $doc->set('field1', 'value1');

        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'blue')));
        $this->assertCount(1, $matches, 'No or too much registered query matched.');
        $this->assertEquals('kuku', $matches[0]['_id'], 'A wrong registered query has matched.');

        // step three: validate that using a different color, no registered query matches.
        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'green')));
        $this->assertCount(0, $matches, 'A registered query matched, although nothing should match at all.');

        $index->delete();
    }

    /**
     * Test case for using filtered percolator queries based on the Elasticsearch documentation examples.
     */
    public function testRegisterAndUnregisterPercolator()
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
        $index->refresh();

        // step two: match a document which should match the kuku query when filtered on the blue color
        $doc = new Document();
        $doc->set('field1', 'value1');

        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'blue')));
        $this->assertCount(1, $matches, 'No or too much registered query matched.');
        $this->assertEquals('kuku', $matches[0]['_id'], 'A wrong registered query has matched.');

        // step three: validate that using a different color, no registered query matches.
        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'green')));
        $this->assertCount(0, $matches, 'A registered query matched, although nothing should match at all.');


        // unregister percolator query
        $response = $percolator->unregisterQuery('kuku');

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        // refreshing is required in order to ensure the query is really ready for execution.
        $index->refresh();

        $matches = $percolator->matchDoc($doc, new Term(array('color' => 'blue')));
        $this->assertCount(0, $matches, 'Percolator query did not get deleted.');

        $index->delete();
    }

    protected function _getDefaultPercolator($percolatorName = 'existingDoc')
    {
        $index = $this->_createIndex();
        $percolator = new Percolator($index);

        $query = new Term(array('name' => 'foobar'));
        $percolator->registerQuery($percolatorName, $query);
        return $percolator;
    }

    protected function _addDefaultDocuments($index, $type='testing')
    {
        $type = $index->getType('testing');
        $doc1 = new Document(1, array('name' => 'foobar'));
        $doc2 = new Document(2, array('name' => 'barbaz'));
        $type->addDocument($doc1);
        $type->addDocument($doc2);
        $index->refresh();
        return $type;
    }

    public function testPercolateExistingDocWithoutAnyParameter()
    {
        $percolator = $this->_getDefaultPercolator();
        $index      = $percolator->getIndex();
        $type       = $this->_addDefaultDocuments($index);

        $matches = $percolator->matchExistingDoc(1, $type->getName());

        $this->assertCount(1, $matches);
        $this->assertEquals('existingDoc', $matches[0]['_id']);
        $index->delete();
    }

    public function testPercolateExistingDocWithPercolateFormatIds()
    {
        $percolator = $this->_getDefaultPercolator();
        $index      = $percolator->getIndex();
        $type       = $this->_addDefaultDocuments($index);

        $parameter = array('percolate_format' => 'ids');
        $matches   = $percolator->matchExistingDoc(1, $type->getName(), null, $parameter);

        $this->assertCount(1, $matches);
        $this->assertEquals('existingDoc', $matches[0]);
        $index->delete();
    }

    public function testPercolateExistingDocWithIdThatShouldBeUrlEncoded()
    {
        $percolator = $this->_getDefaultPercolator();
        $index      = $percolator->getIndex();
        $type       = $this->_addDefaultDocuments($index);

        // id with whitespace, should be urlencoded
        $id = "foo bar 1";

        $type->addDocument(new Document($id, array('name' => 'foobar')));
        $index->refresh();

        $matches = $percolator->matchExistingDoc($id, $type->getName());

        $this->assertCount(1, $matches);
        $index->delete();
    }
}
