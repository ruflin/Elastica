<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Exists;
use Elastica\Filter\Ids;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query\ConstantScore;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class ConstantScoreTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testArrayConstruct()
    {
        $query = new ConstantScore(array('test'));
        $this->assertSame($query->getParam('filter'), array('test'));
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testConstructInvalid()
    {
        new ConstantScore($this);
    }

    /**
     * @group unit
     */
    public function testConstructWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $errorsCollector = $this->startCollectErrors();
        new ConstantScore($existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\ConstantScore passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\ConstantScore::setFilter passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testSetFilterInvalid()
    {
        $query = new ConstantScore();
        $query->setFilter($this);
    }

    /**
     * @group unit
     */
    public function testSetFilterWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new ConstantScore();

        $errorsCollector = $this->startCollectErrors();
        $query->setFilter($existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\ConstantScore::setFilter passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    public function dataProviderSampleQueries()
    {
        return array(
            array(
                new \Elastica\Query\Term(array('foo', 'bar')),
                array(
                    'constant_score' => array(
                        'filter' => array(
                            'term' => array(
                                'foo',
                                'bar',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'and' => array(
                        array(
                            'query' => array(
                                'query_string' => array(
                                    'query' => 'foo',
                                    'default_field' => 'something',
                                ),
                            ),
                        ),
                        array(
                            'query' => array(
                                'query_string' => array(
                                    'query' => 'bar',
                                    'default_field' => 'something',
                                ),
                            ),
                        ),
                    ),
                ),
                '{"constant_score":{"filter":{"and":[{"query":{"query_string":{"query":"foo","default_field":"something"}}},{"query":{"query_string":{"query":"bar","default_field":"something"}}}]}}}',
            ),
        );
    }

    /**
     * @group unit
     * @dataProvider dataProviderSampleQueries
     */
    public function testSimple($filter, $expected)
    {
        $query = new ConstantScore();

        $query->setFilter($filter);

        if (is_string($expected)) {
            $expected = json_decode($expected, true);
        }
        $this->assertEquals($expected, $query->toArray());
    }

    public function dataProviderSampleQueriesWithLegacyFilter()
    {
        $this->hideDeprecated();
        $legacyFilter = new Term(array('foo', 'bar'));
        $this->showDeprecated();

        return array(
            array(
                $legacyFilter,
                array(
                    'constant_score' => array(
                        'filter' => array(
                            'term' => array(
                                'foo',
                                'bar',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @group unit
     * @dataProvider dataProviderSampleQueriesWithLegacyFilter
     */
    public function testSimpleWithLegacyFilter($filter, $expected)
    {
        $query = new ConstantScore();
        $this->hideDeprecated();
        $query->setFilter($filter);
        $this->showDeprecated();

        if (is_string($expected)) {
            $expected = json_decode($expected, true);
        }
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new ConstantScore();

        $boost = 1.2;
        $filter = new \Elastica\Query\Ids();
        $filter->setIds(array(1));
        $query->setFilter($filter);
        $query->setBoost($boost);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
                'boost' => $boost,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayWithLegacyFilter()
    {
        $query = new ConstantScore();

        $boost = 1.2;
        $this->hideDeprecated();
        $filter = new Ids();
        $filter->setIds(array(1));
        $query->setFilter($filter);
        $this->showDeprecated();
        $query->setBoost($boost);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
                'boost' => $boost,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $filter = new \Elastica\Query\Ids();
        $filter->setIds(array(1));

        $query = new ConstantScore($filter);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testConstructWithLegacyFilter()
    {
        $filter = new Ids();
        $filter->setIds(array(1));

        $this->hideDeprecated();
        $query = new ConstantScore($filter);
        $this->showDeprecated();

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testQuery()
    {
        $index = $this->_createIndex();

        $type = $index->getType('constant_score');
        $type->addDocuments(array(
            new Document(1, array('id' => 1, 'email' => 'hans@test.com', 'username' => 'hans')),
            new Document(2, array('id' => 2, 'email' => 'emil@test.com', 'username' => 'emil')),
            new Document(3, array('id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth')),
        ));

        // Refresh index
        $index->refresh();

        $boost = 1.3;
        $query_match = new MatchAll();

        $query = new ConstantScore();
        $query->setQuery($query_match);
        $query->setBoost($boost);

        $expectedArray = array(
            'constant_score' => array(
                'query' => $query_match->toArray(),
                'boost' => $boost,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
        $resultSet = $type->search($query);

        $results = $resultSet->getResults();

        $this->assertEquals($resultSet->count(), 3);
        $this->assertEquals($results[1]->getScore(), 1);
    }

    /**
     * @group unit
     */
    public function testConstructEmpty()
    {
        $query = new ConstantScore();
        $expectedArray = array('constant_score' => array());

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
