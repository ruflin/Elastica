<?php

namespace Elastica\Test;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Percolator;
use Elastica\Query\Term;
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
        $this->assertEquals(1, count($matches1));

        $matches2 = $percolator->matchDoc($doc2);
        $this->assertEmpty($matches2);
    }
}
