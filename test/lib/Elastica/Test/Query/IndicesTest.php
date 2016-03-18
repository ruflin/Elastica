<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Indices;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;

class IndicesTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $expected = array(
            'indices' => array(
                'indices' => array('index1', 'index2'),
                'query' => array(
                    'term' => array('tag' => 'wow'),
                ),
                'no_match_query' => array(
                    'term' => array('tag' => 'such filter'),
                ),
            ),
        );
        $query = new Indices(new Term(array('tag' => 'wow')), array('index1', 'index2'));
        $query->setNoMatchQuery(new Term(array('tag' => 'such filter')));
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testIndicesQuery()
    {
        $docs = array(
            new Document(1, array('color' => 'blue')),
            new Document(2, array('color' => 'green')),
            new Document(3, array('color' => 'blue')),
            new Document(4, array('color' => 'yellow')),
        );

        $index1 = $this->_createIndex();
        $index1->addAlias('indices_query');
        $index1->getType('test')->addDocuments($docs);
        $index1->refresh();

        $index2 = $this->_createIndex();
        $index2->addAlias('indices_query');
        $index2->getType('test')->addDocuments($docs);
        $index2->refresh();

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMustNot(new Term(array('color' => 'blue')));

        $indicesQuery = new Indices($boolQuery, array($index1->getName()));

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMustNot(new Term(array('color' => 'yellow')));
        $indicesQuery->setNoMatchQuery($boolQuery);

        $query = new Query();
        $query->setPostFilter($indicesQuery);

        // search over the alias
        $index = $this->_getClient()->getIndex('indices_query');
        $results = $index->search($query);

        // ensure that the proper docs have been filtered out for each index
        $this->assertEquals(5, $results->count());
        foreach ($results->getResults() as $result) {
            $data = $result->getData();
            $color = $data['color'];
            if ($result->getIndex() === $index1->getName()) {
                $this->assertNotEquals('blue', $color);
            } else {
                $this->assertNotEquals('yellow', $color);
            }
        }
    }

    /**
     * @group unit
     */
    public function testSetIndices()
    {
        $client = $this->_getClient();
        $index1 = $client->getIndex('index1');
        $index2 = $client->getIndex('index2');

        $indices = array('one', 'two');
        $query = new Indices(new Term(array('color' => 'blue')), $indices);
        $this->assertEquals($indices, $query->getParam('indices'));

        $indices[] = 'three';
        $query->setIndices($indices);
        $this->assertEquals($indices, $query->getParam('indices'));

        $query->setIndices(array($index1, $index2));
        $expected = array($index1->getName(), $index2->getName());
        $this->assertEquals($expected, $query->getParam('indices'));

        $returnValue = $query->setIndices($indices);
        $this->assertInstanceOf('Elastica\Query\Indices', $returnValue);
    }

    /**
     * @group unit
     */
    public function testAddIndex()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('someindex');

        $query = new Indices(new Term(array('color' => 'blue')), array());

        $query->addIndex($index);
        $expected = array($index->getName());
        $this->assertEquals($expected, $query->getParam('indices'));

        $query->addIndex('foo');
        $expected = array($index->getName(), 'foo');
        $this->assertEquals($expected, $query->getParam('indices'));

        $returnValue = $query->addIndex('bar');
        $this->assertInstanceOf('Elastica\Query\Indices', $returnValue);
    }
}
