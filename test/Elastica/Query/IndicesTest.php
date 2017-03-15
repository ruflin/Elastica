<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Indices;
use Elastica\Query\Term;
use Elastica\Test\DeprecatedClassBase;

class IndicesTest extends DeprecatedClassBase
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $expected = [
            'indices' => [
                'indices' => ['index1', 'index2'],
                'query' => [
                    'term' => ['tag' => 'wow'],
                ],
                'no_match_query' => [
                    'term' => ['tag' => 'such filter'],
                ],
            ],
        ];
        $query = new Indices(new Term(['tag' => 'wow']), ['index1', 'index2']);
        $query->setNoMatchQuery(new Term(['tag' => 'such filter']));
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testIndicesQuery()
    {
        $docs = [
            new Document(1, ['color' => 'blue']),
            new Document(2, ['color' => 'green']),
            new Document(3, ['color' => 'blue']),
            new Document(4, ['color' => 'yellow']),
        ];

        $index1 = $this->_createIndex();
        $index1->addAlias('indices_query');
        $index1->getType('test')->addDocuments($docs);
        $index1->refresh();

        $index2 = $this->_createIndex();
        $index2->addAlias('indices_query');
        $index2->getType('test')->addDocuments($docs);
        $index2->refresh();

        $boolQuery = new BoolQuery();
        $boolQuery->addMustNot(new Term(['color' => 'blue']));

        $indicesQuery = new Indices($boolQuery, [$index1->getName()]);

        $boolQuery = new BoolQuery();
        $boolQuery->addMustNot(new Term(['color' => 'yellow']));
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

        $indices = ['one', 'two'];
        $query = new Indices(new Term(['color' => 'blue']), $indices);
        $this->assertEquals($indices, $query->getParam('indices'));

        $indices[] = 'three';
        $query->setIndices($indices);
        $this->assertEquals($indices, $query->getParam('indices'));

        $query->setIndices([$index1, $index2]);
        $expected = [$index1->getName(), $index2->getName()];
        $this->assertEquals($expected, $query->getParam('indices'));

        $returnValue = $query->setIndices($indices);
        $this->assertInstanceOf(Indices::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testAddIndex()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('someindex');

        $query = new Indices(new Term(['color' => 'blue']), []);

        $query->addIndex($index);
        $expected = [$index->getName()];
        $this->assertEquals($expected, $query->getParam('indices'));

        $query->addIndex('foo');
        $expected = [$index->getName(), 'foo'];
        $this->assertEquals($expected, $query->getParam('indices'));

        $returnValue = $query->addIndex('bar');
        $this->assertInstanceOf(Indices::class, $returnValue);
    }
}
