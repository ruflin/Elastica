<?php
/**
 * User: Joe Linn
 * Date: 9/16/13
 * Time: 5:05 PM
 */
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Term;
use Elastica\Query\FunctionScore;
use Elastica\Script;
use Elastica\Test\Base as BaseTest;

class FunctionScoreTest extends BaseTest
{
    /**
     * @var \Elastica\Index
     */
    protected $index;

    /**
     * @var \Elastica\Type
     */
    protected $type;

    protected $locationOrigin = "32.804654, -117.242594";

    protected function setUp()
    {
        parent::setUp();
        $this->index = $this->_createIndex();
        $this->type = $this->index->getType('test');
        $this->type->setMapping(array(
            'name' => array('type' => 'string', 'index' => 'not_analyzed'),
            'location' => array('type' => 'geo_point'),
            'price' => array('type' => 'float'),
        ));

        $this->type->addDocument(new Document(1, array(
            'name' => "Mr. Frostie's",
            'location' => array('lat' => 32.799605, 'lon' => -117.243027),
            'price' => 4.5,
        )));
        $this->type->addDocument(new Document(2, array(
            'name' => "Miller's Field",
            'location' => array('lat' => 32.795964, 'lon' => -117.255028),
            'price' => 9.5,
        )));

        $this->index->refresh();
    }

    public function testToArray()
    {
        $priceOrigin = 0;
        $locationScale = '2mi';
        $priceScale = 9.25;
        $query = new FunctionScore();
        $childQuery = new \Elastica\Query\MatchAll();
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

    public function testDecayWeight()
    {
        $priceOrigin = 0;
        $locationScale = '2mi';
        $priceScale = 9.25;
        $query = new FunctionScore();
        $childQuery = new \Elastica\Query\MatchAll();
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

    public function testGauss()
    {
        $query = new FunctionScore();
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'location', $this->locationOrigin, "4mi");
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', 0, 10);
        $response = $this->type->search($query);
        $results = $response->getResults();

        // the document with the closest location and lowest price should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

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

        $response = $this->type->search($query);
        $results = $response->getResults();

        // the document with price = 4.5 should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

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

        $response = $this->type->search($query);
        $results = $response->getResults();

        // the document with the random score should have a score > 1, means it is the first result
        $result0 = $results[0]->getData();

        $this->assertEquals("Miller's Field", $result0['name']);
    }

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

    public function testRandomScoreWithoutSeed()
    {
        $query = new FunctionScore();
        $query->setRandomScore();

        $response = $this->type->search($query);

        $this->assertEquals(2, $response->count());
    }

    public function testScriptScore()
    {
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

        $response = $this->type->search($query);
        $results = $response->getResults();

        // the document the highest price should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Miller's Field", $result0['name']);
    }
}
