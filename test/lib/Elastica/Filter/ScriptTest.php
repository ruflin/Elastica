<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_ScriptTest extends Elastica_Test
{
    public function testToArray()
    {
        $string = '_score * 2.0';

        $filter = new Elastica_Filter_Script($string);

        $array = $filter->toArray();
        $this->assertInternalType('array', $array);

        $expected = array(
            'script' => array(
                'script' => $string,
            )
        );
        $this->assertEquals($expected, $array);
    }

    public function testSetScript()
    {
        $string = '_score * 2.0';
        $params = array(
            'param1' => 'one',
            'param2' => 1,
        );
        $lang = 'mvel';
        $script = new Elastica_Script($string, $params, $lang);

        $filter = new Elastica_Filter_Script();
        $filter->setScript($script);

        $array = $filter->toArray();

        $expected = array(
            'script' => array(
                'script' => $string,
                'params' => $params,
                'lang' => $lang,
            )
        );
        $this->assertEquals($expected, $array);
    }

    public function testSetQuery()
    {
        $string = '_score * 2.0';
        $query = array(
            'script' => $string,
        );
        $script = new Elastica_Filter_Script();
        $script->setQuery($query);

        $expected = array(
            'script' => array(
                'script' => $string,
            )
        );

        $this->assertEquals($expected, $script->toArray());
    }
}
