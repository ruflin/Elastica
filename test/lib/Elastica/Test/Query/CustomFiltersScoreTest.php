<?php

namespace Elastica\Test\Query;

use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query\CustomFiltersScore;
use Elastica\Query\QueryString;
use Elastica\Script;
use Elastica\Test\Base as BaseTest;

class CustomFiltersScoreTest extends BaseTest
{
    public function testConstructor()
    {
        $query = new QueryString('elastica');
        $customFiltersScoreQuery = new CustomFiltersScore($query);

        $expected = array(
            'custom_filters_score' => array(
                'query' => $query->toArray(),
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testEmptyConstructor()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass,
                ),
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testSetQuery()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $query = new QueryString('elastica');
        $customFiltersScoreQuery->setQuery($query);

        $expected = array(
            'custom_filters_score' => array(
                'query' => $query->toArray(),
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    /**
     * @expectedException \Elastica\Exception\NotImplementedException
     */
    public function testSetQueryInvalid()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $query = new \stdClass();
        $customFiltersScoreQuery->setQuery($query);
    }

    public function testAddFilter()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $rangeFilter = new Range('age', array('from' => 20, 'to' => 30));
        $rangeBoost = 2.5;
        $customFiltersScoreQuery->addFilter($rangeFilter, $rangeBoost);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'boost' => $rangeBoost,
                    )
                )
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());

        $termFilter = new Term();
        $termFilter->setTerm('name', 'ruflin');
        $termBoost = 3.0;

        $customFiltersScoreQuery->addFilter($termFilter, $termBoost);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'boost' => $rangeBoost,
                    ),
                    array(
                        'filter' => $termFilter->toArray(),
                        'boost' => $termBoost,
                    )
                )
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testAddFilterScript()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $rangeFilter = new Range('age', array('from' => 20, 'to' => 30));
        $rangeScript = "doc['num1'].value > 1";
        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'script' => $rangeScript,
                    )
                )
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());

        $customFiltersScoreQuery = new CustomFiltersScore();

        $script = "doc['num1'].value > 1";
        $rangeScript = new Script($script);
        $rangeScript->setParam('param1', 1);
        $rangeScript->setLang(Script::LANG_GROOVY);

        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'script' => $script,
                    )
                )
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());

        $termFilter = new Term();
        $termFilter->setTerm('name', 'ruflin');
        $termScript = "doc['num2'].value > 1";

        $customFiltersScoreQuery->addFilterScript($termFilter, $termScript);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'script' => $script,
                    ),
                    array(
                        'filter' => $termFilter->toArray(),
                        'script' => $termScript,
                    )
                )
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testSetScriptParams()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $rangeFilter = new Range('age', array('from' => 20, 'to' => 30));
        $rangeScript = "doc['num1'].value > 1";
        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $scriptParams = array(
            'param1' => 1,
            'param2' => 'two',
        );
        $customFiltersScoreQuery->setScriptParams($scriptParams);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'script' => $rangeScript,
                    )
                ),
                'params' => $scriptParams
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testSetScriptLang()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $rangeFilter = new Range('age', array('from' => 20, 'to' => 30));
        $rangeScript = "doc['num1'].value > 1";
        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $scriptLang = Script::LANG_GROOVY;
        $customFiltersScoreQuery->setScriptLang($scriptLang);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'script' => $rangeScript,
                    )
                ),
                'lang' => $scriptLang
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testSetScoreMode()
    {
        $customFiltersScoreQuery = new CustomFiltersScore();

        $rangeFilter = new Range('age', array('from' => 20, 'to' => 30));
        $rangeBoost = 2.5;
        $customFiltersScoreQuery->addFilter($rangeFilter, $rangeBoost);

        $customFiltersScoreQuery->setScoreMode(CustomFiltersScore::SCORE_MODE_TOTAL);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new \stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'boost' => $rangeBoost,
                    )
                ),
                'score_mode' => CustomFiltersScore::SCORE_MODE_TOTAL,
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }
}
