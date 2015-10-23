<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Term;
use Elastica\Query\FunctionScore;
use Elastica\Query\MatchAll;
use Elastica\Script;
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
     * @group functional
     */
    public function testWeight()
    {
        $filter = new Term(array('price' => 4.5));
        $query = new FunctionScore();
        $query->addBoostFactorFunction(5.0, $filter);
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
        $filter = new Term(array('price' => 4.5));
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
     * @group unit
     */
    public function testRandomScoreWeight()
    {
        $filter = new Term(array('price' => 4.5));
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
