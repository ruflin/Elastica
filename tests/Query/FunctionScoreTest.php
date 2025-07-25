<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query\FunctionScore;
use Elastica\Query\MatchAll;
use Elastica\Query\Term;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class FunctionScoreTest extends BaseTest
{
    protected $locationOrigin = '32.804654, -117.242594';

    #[Group('unit')]
    public function testToArray(): void
    {
        $priceOrigin = 0;
        $locationScale = '2mi';
        $priceScale = 9.25;
        $query = new FunctionScore();
        $childQuery = new MatchAll();
        $query->setQuery($childQuery);
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'location', $this->locationOrigin, $locationScale);
        $query->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', $priceOrigin, $priceScale);
        $expected = [
            'function_score' => [
                'query' => $childQuery->toArray(),
                'functions' => [
                    [
                        'gauss' => [
                            'location' => [
                                'origin' => $this->locationOrigin,
                                'scale' => $locationScale,
                            ],
                        ],
                    ],
                    [
                        'gauss' => [
                            'price' => [
                                'origin' => $priceOrigin,
                                'scale' => $priceScale,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testDecayWeight(): void
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
            .5,
            null,
            FunctionScore::MULTI_VALUE_MODE_AVG
        );
        $query->addDecayFunction(
            FunctionScore::DECAY_GAUSS,
            'price',
            $priceOrigin,
            $priceScale,
            null,
            null,
            2,
            null,
            FunctionScore::MULTI_VALUE_MODE_MAX
        );
        $expected = [
            'function_score' => [
                'query' => $childQuery->toArray(),
                'functions' => [
                    [
                        'gauss' => [
                            'location' => [
                                'origin' => $this->locationOrigin,
                                'scale' => $locationScale,
                            ],
                            'multi_value_mode' => FunctionScore::MULTI_VALUE_MODE_AVG,
                        ],
                        'weight' => .5,
                    ],
                    [
                        'gauss' => [
                            'price' => [
                                'origin' => $priceOrigin,
                                'scale' => $priceScale,
                            ],
                            'multi_value_mode' => FunctionScore::MULTI_VALUE_MODE_MAX,
                        ],
                        'weight' => 2,
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $query->toArray());
    }

    #[Group('functional')]
    public function testGauss(): void
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

    #[Group('functional')]
    public function testGaussMultiValue(): void
    {
        $query = new FunctionScore();
        $query->addDecayFunction(
            FunctionScore::DECAY_GAUSS,
            'location',
            $this->locationOrigin,
            '4mi',
            null,
            null,
            null,
            null,
            FunctionScore::MULTI_VALUE_MODE_SUM
        );
        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the sum of distances should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Miller's Field", $result0['name']);
    }

    #[Group('unit')]
    public function testAddWeightFunction(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addWeightFunction(5.0, $filter);

        $sameFilter = new Term(['price' => 4.5]);
        $sameQuery = new FunctionScore();
        $sameQuery->addWeightFunction(5.0, $sameFilter);

        $this->assertEquals($query->toArray(), $sameQuery->toArray());
    }

    #[Group('unit')]
    public function testLegacyFilterAddWeightFunction(): void
    {
        $query = new FunctionScore();
        $filter = new Term(['price' => 4.5]);
        $query->addWeightFunction(5.0, $filter);

        $sameQuery = new FunctionScore();
        $sameFilter = new Term(['price' => 4.5]);
        $sameQuery->addWeightFunction(5.0, $sameFilter);

        $this->assertEquals($query->toArray(), $sameQuery->toArray());
    }

    #[Group('functional')]
    public function testWeight(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addWeightFunction(5.0, $filter);

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'weight' => 5.0,
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with price = 4.5 should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

    #[Group('functional')]
    public function testWeightWithLegacyFilter(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addWeightFunction(5.0, $filter);

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'weight' => 5.0,
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with price = 4.5 should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);
    }

    #[Group('functional')]
    public function testRandomScore(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter, null, '_id');

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'random_score' => [
                            'seed' => 2,
                            'field' => '_id',
                        ],
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the random score should have a score > 1, means it is the first result
        $result0 = $results[0]->getData();

        $this->assertEquals("Miller's Field", $result0['name']);
    }

    #[Group('functional')]
    public function testRandomScoreWithLegacyFilter(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter, null, '_id');

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'random_score' => [
                            'seed' => 2,
                            'field' => '_id',
                        ],
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the random score should have a score > 1, means it is the first result
        $result0 = $results[0]->getData();

        $this->assertEquals("Miller's Field", $result0['name']);
    }

    #[Group('unit')]
    public function testRandomScoreWeight(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter, 2, '_id');

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'random_score' => [
                            'seed' => 2,
                            'field' => '_id',
                        ],
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                        'weight' => 2,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testRandomScoreWeightWithLegacyFilter(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter, 2, '_id');

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'random_score' => [
                            'seed' => 2,
                            'field' => '_id',
                        ],
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                        'weight' => 2,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());
    }

    #[Group('functional')]
    public function testRandomScoreWithoutSeed(): void
    {
        $query = new FunctionScore();
        $query->setRandomScore();

        $response = $this->_getIndexForTest()->search($query);

        $this->assertEquals(2, $response->count());
    }

    #[Group('functional')]
    public function testRandomScoreWithoutField(): void
    {
        $filter = new Term(['price' => 4.5]);
        $query = new FunctionScore();
        $query->addRandomScoreFunction(2, $filter);

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'random_score' => [
                            'seed' => 2,
                        ],
                        'filter' => [
                            'term' => [
                                'price' => 4.5,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the random score should have a score > 1, means it is the first result
        $result0 = $results[0]->getData();

        $this->assertEquals("Miller's Field", $result0['name']);
    }

    #[Group('functional')]
    public function testScriptScore(): void
    {
        $scriptString = "_score * doc['price'].value";
        $script = new Script($scriptString, null, Script::LANG_PAINLESS);
        $query = new FunctionScore();
        $query->addScriptScoreFunction($script);
        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'script_score' => [
                            'script' => [
                                'source' => $scriptString,
                                'lang' => Script::LANG_PAINLESS,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document the highest price should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Miller's Field", $result0['name']);
    }

    #[Group('functional')]
    public function testSetMinScore(): void
    {
        $expected = [
            'function_score' => [
                'min_score' => 0.8,
                'functions' => [
                    [
                        'gauss' => [
                            'price' => [
                                'origin' => 0,
                                'scale' => 10,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $query = (new FunctionScore())
            ->addDecayFunction(FunctionScore::DECAY_GAUSS, 'price', 0, 10)
            ->setMinScore(0.8)
        ;

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]->getId());
    }

    #[Group('functional')]
    public function testFieldValueFactor(): void
    {
        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'field_value_factor' => [
                            'field' => 'popularity',
                            'factor' => 1.2,
                            'modifier' => 'sqrt',
                            'missing' => 0.1,    // available from >=1.6
                        ],
                    ],
                ],
            ],
        ];

        $query = new FunctionScore();
        $query->addFieldValueFactorFunction('popularity', 1.2, FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT, 0.1);

        $this->assertEquals($expected, $query->toArray());

        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        $this->assertCount(2, $results);
        $this->assertEquals(2, $results[0]->getId());
    }

    /**
     * @group unit
     */
    public function testDecayExponential(): void
    {
        $query = new FunctionScore();
        $query->addDecayFunction(FunctionScore::DECAY_EXPONENTIAL, 'location', $this->locationOrigin, '4mi');
        $query->addDecayFunction(FunctionScore::DECAY_EXPONENTIAL, 'price', 0, 10);

        $expected = [
            'function_score' => [
                'functions' => [
                    [
                        'exp' => [
                            'location' => [
                                'origin' => $this->locationOrigin,
                                'scale' => '4mi',
                            ],
                        ],
                    ],
                    [
                        'exp' => [
                            'price' => [
                                'origin' => 0,
                                'scale' => 10,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testDecayExponentialFunctional(): void
    {
        $query = new FunctionScore();
        $query->addDecayFunction(FunctionScore::DECAY_EXPONENTIAL, 'location', $this->locationOrigin, '4mi');
        $query->addDecayFunction(FunctionScore::DECAY_EXPONENTIAL, 'price', 0, 10);
        $response = $this->_getIndexForTest()->search($query);
        $results = $response->getResults();

        // the document with the closest location and lowest price should be scored highest
        $result0 = $results[0]->getData();
        $this->assertEquals("Mr. Frostie's", $result0['name']);

        // Verify that we got results and they are properly scored
        $this->assertGreaterThan(0, $results[0]->getScore());
        $this->assertCount(2, $results);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'name' => ['type' => 'text', 'index' => 'false'],
            'location' => ['type' => 'geo_point'],
            'price' => ['type' => 'float'],
            'popularity' => ['type' => 'integer'],
        ]));

        $index->addDocuments([
            new Document('1', [
                'name' => "Mr. Frostie's",
                'location' => [['lat' => 32.799605, 'lon' => -117.243027], ['lat' => 32.792744, 'lon' => -117.2387341]],
                'price' => 4.5,
                'popularity' => null,
            ]),
            new Document('2', [
                'name' => "Miller's Field",
                'location' => ['lat' => 32.795964, 'lon' => -117.255028],
                'price' => 9.5,
                'popularity' => 1,
            ]),
        ]);

        $index->refresh();

        return $index;
    }
}
