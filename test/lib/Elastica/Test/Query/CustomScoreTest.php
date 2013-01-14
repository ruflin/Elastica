<?php

namespace Elastica\Test\Query;

use Elastica\Query\MatchAllQuery;
use Elastica\Query\CustomScoreQuery;
use Elastica\Script;
use Elastica\Test\Base as BaseTest;

class CustomScoreTest extends BaseTest
{
    public function testCustomScoreQuery()
    {
        $query = new MatchAllQuery();

        $customScoreQuery = new CustomScoreQuery();
        $customScoreQuery->setQuery($query);
        $customScoreQuery->setScript("doc['hits'].value * (param1 + param2)");
        $customScoreQuery->addParams(array('param1' => 1123, 'param2' => 2001));

        $expected = array(
            'custom_score' => array(
                'query' => array(
                    'match_all' => new \stdClass,
                ),
                'script' => "doc['hits'].value * (param1 + param2)",
                'params' => array(
                    'param1' => 1123,
                    'param2' => 2001,
                )
            )
        );

        $this->assertEquals($expected, $customScoreQuery->toArray());
    }

    public function testSetScript()
    {
        $string = '_score * 2.0';
        $lang = 'mvel';
        $params = array(
            'param1' => 'one',
            'param2' => 1,
        );
        $script = new Script($string);
        $script->setLang('mvel');
        $script->setParams($params);

        $customScoreQuery = new CustomScoreQuery();
        $customScoreQuery->setScript($script);

        $expected = array(
            'custom_score' => array(
                'query' => array(
                    'match_all' => new \stdClass,
                ),
                'script' => $string,
                'params' => $params,
                'lang' => $lang,
            )
        );

        $this->assertEquals($expected, $customScoreQuery->toArray());
    }

    public function testConstructor()
    {
        $string = '_score * 2.0';
        $customScoreQuery = new CustomScoreQuery($string);

        $expected = array(
            'custom_score' => array(
                'query' => array(
                    'match_all' => new \stdClass,
                ),
                'script' => $string,
            )
        );

        $this->assertEquals($expected, $customScoreQuery->toArray());
    }
}
