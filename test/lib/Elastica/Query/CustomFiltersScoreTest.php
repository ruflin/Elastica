<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_CustomFiltersScoreTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $query = new Elastica_Query_QueryString('elastica');
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore($query);

        $expected = array(
            'custom_filters_score' => array(
                'query' => $query->toArray(),
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testEmptyConstructor()
    {
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass,
                ),
            )
        );

        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    public function testSetQuery()
    {
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $query = new Elastica_Query_QueryString('elastica');
        $customFiltersScoreQuery->setQuery($query);

        $expected = array(
            'custom_filters_score' => array(
                'query' => $query->toArray(),
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }

    /**
     * @expectedException Elastica_Exception_NotImplemented
     */
    public function testSetQueryInvalid()
    {
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $query = new stdClass();
        $customFiltersScoreQuery->setQuery($query);
    }

    public function testAddFilter()
    {
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $rangeFilter = new Elastica_Filter_Range('age', array('from' => 20, 'to' => 30));
        $rangeBoost = 2.5;
        $customFiltersScoreQuery->addFilter($rangeFilter, $rangeBoost);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
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

        $termFilter = new Elastica_Filter_Term();
        $termFilter->setTerm('name', 'ruflin');
        $termBoost = 3.0;

        $customFiltersScoreQuery->addFilter($termFilter, $termBoost);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
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
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $rangeFilter = new Elastica_Filter_Range('age', array('from' => 20, 'to' => 30));
        $rangeScript = "doc['num1'].value > 1";
        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
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

        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $script = "doc['num1'].value > 1";
        $rangeScript = new Elastica_Script($script);
        $rangeScript->setParam('param1', 1);
        $rangeScript->setLang(Elastica_Script::LANG_GROOVY);

        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
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

        $termFilter = new Elastica_Filter_Term();
        $termFilter->setTerm('name', 'ruflin');
        $termScript = "doc['num2'].value > 1";

        $customFiltersScoreQuery->addFilterScript($termFilter, $termScript);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
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
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $rangeFilter = new Elastica_Filter_Range('age', array('from' => 20, 'to' => 30));
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
                    'match_all' => new stdClass(),
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
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $rangeFilter = new Elastica_Filter_Range('age', array('from' => 20, 'to' => 30));
        $rangeScript = "doc['num1'].value > 1";
        $customFiltersScoreQuery->addFilterScript($rangeFilter, $rangeScript);

        $scriptLang = Elastica_Script::LANG_GROOVY;
        $customFiltersScoreQuery->setScriptLang($scriptLang);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
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
        $customFiltersScoreQuery = new Elastica_Query_CustomFiltersScore();

        $rangeFilter = new Elastica_Filter_Range('age', array('from' => 20, 'to' => 30));
        $rangeBoost = 2.5;
        $customFiltersScoreQuery->addFilter($rangeFilter, $rangeBoost);

        $customFiltersScoreQuery->setScoreMode(Elastica_Query_CustomFiltersScore::SCORE_MODE_TOTAL);

        $expected = array(
            'custom_filters_score' => array(
                'query' => array(
                    'match_all' => new stdClass(),
                ),
                'filters' => array(
                    array(
                        'filter' => $rangeFilter->toArray(),
                        'boost' => $rangeBoost,
                    )
                ),
                'score_mode' => Elastica_Query_CustomFiltersScore::SCORE_MODE_TOTAL,
            )
        );
        $this->assertEquals($expected, $customFiltersScoreQuery->toArray());
    }
}
