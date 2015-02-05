<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Boosting;
use Elastica\Test\Base as BaseTest;

class BoostingTest extends BaseTest
{
    /**
     * @var \Elastica\Index
     */
    protected $index;

    /**
     * @var \Elastica\Type
     */
    protected $type;

    /**
     * @var array
     */
    protected $sampleData;

    protected function setUp()
    {
        parent::setUp();
        $this->index = $this->_createIndex();
        $this->type = $this->index->getType('test');
        $this->type->setMapping(array(
            'name' => array('type' => 'string', 'index' => 'analyzed'),
            'price' => array('type' => 'float'),
        ));

        $this->sampleData = array(
            array("name" => "Vital Lama", "price" => 5.2),
            array("name" => "Vital Match", "price" => 2.1),
            array("name" => "Mercury Vital", "price" => 7.5),
            array("name" => "Fist Mercury", "price" => 3.8),
            array("name" => "Lama Vital 2nd", "price" => 3.2),
        );

        foreach ($this->sampleData as $key => $value) {
            $this->type->addDocument(new Document($key, $value));
        }

        $this->index->refresh();
    }

    public function testToArray()
    {
        $keyword = "vital";
        $negativeKeyword = "Mercury";

        $query = new Boosting();
        $positiveQuery = new \Elastica\Query\Term(array('name' => $keyword));
        $negativeQuery = new \Elastica\Query\Term(array('name' => $negativeKeyword));
        $query->setPositiveQuery($positiveQuery);
        $query->setNegativeQuery($negativeQuery);
        $query->setNegativeBoost(0.3);

        $expected = array(
            'boosting' => array(
                'positive' => $positiveQuery->toArray(),
                'negative' => $negativeQuery->toArray(),
                'negative_boost' => 0.3,
            ),
        );
        $this->assertEquals($expected, $query->toArray());
    }

    public function testNegativeBoost()
    {
        $keyword = "vital";
        $negativeKeyword = "mercury";

        $query = new Boosting();
        $positiveQuery = new \Elastica\Query\Term(array('name' => $keyword));
        $negativeQuery = new \Elastica\Query\Term(array('name' => $negativeKeyword));
        $query->setPositiveQuery($positiveQuery);
        $query->setNegativeQuery($negativeQuery);
        $query->setNegativeBoost(0.2);

        $response = $this->type->search($query);
        $results = $response->getResults();

        $this->assertEquals($response->getTotalHits(), 4);

        $lastResult = $results[3]->getData();
        $this->assertEquals($lastResult['name'], $this->sampleData[2]['name']);
    }
}
