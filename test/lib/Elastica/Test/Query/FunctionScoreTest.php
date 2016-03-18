<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Exists;
use Elastica\Filter\Term;
use Elastica\Query\FunctionScore;
use Elastica\Query\MatchAll;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;

class FunctionScoreTest extends BaseTest
{
    protected $locationOrigin = '32.804654, -117.242594';

    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(array(
            'name' => array('type' => 'string', 'index' => 'not_analyzed'),
            'location' => array('type' => 'geo_point'),
            'price' => array('type' => 'float'),
            'popularity' => array('type' => 'integer'),
        ));

        $type->addDocuments(array(
            new Document(1, array(
                'name' => "Mr. Frostie's",
                'location' => array('lat' => 32.799605, 'lon' => -117.243027),
                'price' => 4.5,
                'popularity' => null,
            )),
            new Document(2, array(
                'name' => "Miller's Field",
                'location' => array('lat' => 32.795964, 'lon' => -117.255028),
                'price' => 9.5,
                'popularity' => 1,
            )),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\DeprecatedException
     */
    public function testSetFilterWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $query->setFilter($existsFilter);
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addFunction('f', 1, $this);
    }

    /**
     * @group unit
     */
    public function testAddFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addFunction('f', 1, $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddDecayFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'location', $this->locationOrigin, '2mi', null, null, null, $this);
    }

    /**
     * @group unit
     */
    public function testAddDecayFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'location', $this->locationOrigin, '2mi', null, null, null, $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addDecayFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testScriptScoreFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addScriptScoreFunction(new Script('t'), $this);
    }

    /**
     * @group unit
     */
    public function testScriptScoreFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addScriptScoreFunction(new Script('t'), $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addScriptScoreFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddFieldValueFactorFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addFieldValueFactorFunction('popularity', 1.2, FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT, 0.1, null, $this);
    }

    /**
     * @group unit
     */
    public function testAddFieldValueFactorFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addFieldValueFactorFunction('popularity', 1.2, FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT, 0.1, null, $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addFieldValueFactorFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddBoostFactorFunctionFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addBoostFactorFunction(5.0, $this);
    }

    /**
     * @group unit
     */
    public function testAddBoostFactorFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addBoostFactorFunction(5.0, $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addBoostFactorFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Query\FunctionScore::addBoostFactorFunction is deprecated. Use addWeightFunction instead. This method will be removed in further Elastica releases',
                'Deprecated: Elastica\Query\FunctionScore::addWeightFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddWeightFunctionFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addWeightFunction(5.0, $this);
    }

    /**
     * @group unit
     */
    public function testAddWeightFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addWeightFunction(5.0, $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addWeightFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddRandomScoreFunctionInvalid()
    {
        $query = new FunctionScore('test');
        $query->addRandomScoreFunction(5.0, $this);
    }

    /**
     * @group unit
     */
    public function testAddRandomScoreFunctionWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new FunctionScore('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addRandomScoreFunction(5.0, $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\FunctionScore::addRandomScoreFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
                'Deprecated: Elastica\Query\FunctionScore::addFunction passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $priceOrigin = 0;
        $locationScale = '2mi';
        $priceScale = 9.25;
        $query = new FunctionScore();
        $childQuery = new MatchAll();
        $query->setQuery($childQuery);
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'location', $this->locationOrigin, $locationScale);
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', $priceOrigin, $priceScale);
        $expected = array(
            'function_score' => array(
                'query' => $childQuery->toArray(),
                'functions' => array(
                    array(
                        'gauss' => array(
                            'location' => array(
                                'origin' => $this->locationOrigin,
                                'scale' => $locationScale,
                            ),
                        ),
                    ),
                    array(
                        'gauss' => array(
                            'price' => array(
                                'origin' => $priceOrigin,
                                'scale' => $priceScale,
                            ),
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testDecayWeight()
    {
        $priceOrigin = 0;
        $locationScale = '2mi';
        $priceScale = 9.25;
        $query = new FunctionScore();
        $childQuery = new MatchAll();
        $query->setQuery($childQuery);
        $query->addDecayFunction(
            FunctionScore::DECAY_GAUSS,
            'location',
            $this->locationOrigin,
            $locationScale,
            null,
            null,
            .5
        );
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', $priceOrigin, $priceScale, null, null, 2);
        $expected = array(
            'function_score' => array(
                'query' => $childQuery->toArray(),
                'functions' => array(
                    array(
                        'gauss' => array(
                            'location' => array(
                                'origin' => $this->locationOrigin,
                                'scale' => $locationScale,
                            ),
                        ),
                        'weight' => .5,
                    ),
                    array(
                        'gauss' => array(
                            'price' => array(
                                'origin' => $priceOrigin,
                                'scale' => $priceScale,
                            ),
                        ),
                        'weight' => 2,
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testGauss()
    {
        $query = new FunctionScore();
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'location', $this->locationOrigin, '4mi');
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', 0, 10);
        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the closest location and lowest price should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

    /**
     * @group unit
     */
    public function testAddBoostFactorFunction()
    {
        $filter = new \Elastica\Query\Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addWeightFunction(5.0, $filter);

        $sameFilter = new \Elastica\Query\Term(array('price' => 4.5));
        $sameQuery = new FunctionScore();
        $this->hideDeprecated();
        $sameQuery->addBoostFactorFunction(5.0, $sameFilter);
        $this->showDeprecated();

        $this->assertEquals($query->toArray(), $sameQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testLegacyFilterAddBoostFactorFunction()
    {
        $query = new FunctionScore();
        $this->hideDeprecated();
        $filter = new Term(array('price' => 4.5));
        $query->addWeightFunction(5.0, $filter);
        $this->showDeprecated();

        $sameQuery = new FunctionScore();
        $this->hideDeprecated();
        $sameFilter = new Term(array('price' => 4.5));
        $sameQuery->addBoostFactorFunction(5.0, $sameFilter);
        $this->showDeprecated();

        $this->assertEquals($query->toArray(), $sameQuery->toArray());
    }

    /**
     * @group functional
     */
    public function testWeight()
    {
        $filter = new \Elastica\Query\Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addWeightFunction(5.0, $filter);

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'weight' => 5.0,
                        'filter' => array(
                            'term' => array(
                                'price' => 4.5,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with price = 4.5 should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

    /**
     * @group functional
     */
    public function testWeightWithLegacyFilter()
    {
        $this->hideDeprecated();
        $filter = new Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addWeightFunction(5.0, $filter);
        $this->showDeprecated();

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'weight' => 5.0,
                        'filter' => array(
                            'term' => array(
                                'price' => 4.5,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with price = 4.5 should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

    /**
     * @group functional
     */
    public function testRandomScore()
    {
        $filter = new \Elastica\Query\Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter);

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'random_score' => array(
                            'seed' => 2,
                        ),
                        'filter' => array(
                            'term' => array(
                                'price' => 4.5,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the random score should have a score > 1, means it is the first result
        $result0 = $results[0]->getData();

        $this->assertEquals("Miller's Field", $result0['name']);
    }

    /**
     * @group functional
     */
    public function testRandomScoreWithLegacyFilter()
    {
        $this->hideDeprecated();
        $filter = new Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter);
        $this->showDeprecated();

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'random_score' => array(
                            'seed' => 2,
                        ),
                        'filter' => array(
                            'term' => array(
                                'price' => 4.5,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the random score should have a score > 1, means it is the first result
        $result0 = $results[0]->getData();

        $this->assertEquals("Miller's Field", $result0['name']);
    }

    /**
     * @group unit
     */
    public function testRandomScoreWeight()
    {
        $filter = new \Elastica\Query\Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter, 2);

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'random_score' => array(
                            'seed' => 2,
                        ),
                        'filter' => array(
                            'term' => array(
                                'price' => 4.5,
                            ),
                        ),
                        'weight' => 2,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testRandomScoreWeightWithLegacyFilter()
    {
        $this->hideDeprecated();
        $filter = new Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter, 2);
        $this->showDeprecated();

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'random_score' => array(
                            'seed' => 2,
                        ),
                        'filter' => array(
                            'term' => array(
                                'price' => 4.5,
                            ),
                        ),
                        'weight' => 2,
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testRandomScoreWithoutSeed()
    {
        $query = new FunctionScore();
        $query->setRandomScore();

        $response = $this->_getIndexForTest()->search($query);

        $this->assertEquals(2, $response->count());
    }

    /**
     * @group functional
     */
    public function testScriptScore()
    {
        $this->_checkScriptInlineSetting();
        $scriptString = "_score * doc['price'].value";
        $script = new Script($scriptString);
        $query = new FunctionScore();
        $query->addScriptScoreFunction($script);
        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'script_score' => array(
                            'script' => $scriptString,
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document the highest price should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Miller's Field", $result0['name']);
    }

    /**
     * @group functional
     */
    public function testSetMinScore()
    {
        $this->_checkVersion('1.5');

        $expected = array(
            'function_score' => array(
                'min_score' => 0.8,
                'functions' => array(
                    array(
                        'gauss' => array(
                            'price' => array(
                                'origin' => 0,
                                'scale' => 10,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $query = new FunctionScore();
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', 0, 10);
        $returnedValue = $query->setMinScore(0.8);

        $this->assertEquals($expected, $query->toArray());
        $this->assertInstanceOf('Elastica\Query\FunctionScore', $returnedValue);

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]->getId());
    }

    /**
     * @group functional
     */
    public function testFieldValueFactor()
    {
        $this->_checkVersion('1.6');

        $expected = array(
            'function_score' => array(
                'functions' => array(
                    array(
                        'field_value_factor' => array(
                            'field' => 'popularity',
                            'factor' => 1.2,
                            'modifier' => 'sqrt',
                            'missing' => 0.1,    // available from >=1.6
                        ),
                    ),
                ),
            ),
        );

        $query = new FunctionScore();
        $query->addFieldValueFactorFunction('popularity', 1.2, FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT, 0.1);

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        $this->assertCount(2, $results);
        $this->assertEquals(2, $results[0]->getId());
    }
}
