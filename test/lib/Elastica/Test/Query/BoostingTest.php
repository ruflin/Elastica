<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Boosting;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;

class BoostingTest extends BaseTest
{
    /**
     * @var array
     */
    protected $sampleData = array(
        array('name' => 'Vital Lama', 'price' => 5.2),
        array('name' => 'Vital Match', 'price' => 2.1),
        array('name' => 'Mercury Vital', 'price' => 7.5),
        array('name' => 'Fist Mercury', 'price' => 3.8),
        array('name' => 'Lama Vital 2nd', 'price' => 3.2),
    );

    protected function _getTestIndex()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $type->setMapping(array(
            'name' => array('type' => 'string', 'index' => 'analyzed'),
            'price' => array('type' => 'float'),
        ));
        $docs = array();
        foreach ($this->sampleData as $key => $value) {
            $docs[] = new Document($key, $value);
        }
        $type->addDocuments($docs);

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $keyword = 'vital';
        $negativeKeyword = 'Mercury';

        $query = new Boosting();
        $positiveQuery = new Term(array('name' => $keyword));
        $negativeQuery = new Term(array('name' => $negativeKeyword));
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

    /**
     * @group functional
     */
    public function testNegativeBoost()
    {
        $keyword = 'vital';
        $negativeKeyword = 'mercury';

        $query = new Boosting();
        $positiveQuery = new Term(array('name' => $keyword));
        $negativeQuery = new Term(array('name' => $negativeKeyword));
        $query->setPositiveQuery($positiveQuery);
        $query->setNegativeQuery($negativeQuery);
        $query->setNegativeBoost(0.2);

        $response = $this->_getTestIndex()->search($query);
        $results = $response->getResults();

        $this->assertEquals($response->getTotalHits(), 4);

        $lastResult = $results[3]->getData();
        $this->assertEquals($lastResult['name'], $this->sampleData[2]['name']);
    }
}
