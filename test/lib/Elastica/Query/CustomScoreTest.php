<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_CustomScoreTest extends PHPUnit_Framework_TestCase
{
    public function testCustomScoreQuery()
    {
        $query = new Elastica_Query_MatchAll();

        $customScoreQuery = new Elastica_Query_CustomScore();
        $customScoreQuery->setQuery($query);
        $customScoreQuery->setScript("doc['hits'].value * (param1 + param2)");
        $customScoreQuery->addParams(array('param1' => 1123, 'param2' => 2001));

        $expected = array(
            'custom_score' => array(
                'query' => array(
                    'match_all' => new stdClass,
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
        $script = new Elastica_Script($string);
        $script->setLang('mvel');
        $script->setParams($params);

        $customScoreQuery = new Elastica_Query_CustomScore();
        $customScoreQuery->setScript($script);

        $expected = array(
            'custom_score' => array(
                'query' => array(
                    'match_all' => new stdClass,
                ),
                'script' => $string,
                'params' => $params,
                'lang' => $lang,
            )
        );

        $this->assertEquals($expected, $customScoreQuery->toArray());
    }
}
