<?php

namespace Elastica\Test\Filter;

use Elastica\Script;
use Elastica\Filter\Script as ScriptFilter;
use Elastica\Test\Base as BaseTest;

class ScriptTest extends BaseTest
{
    public function testToArray()
    {
        $string = '_score * 2.0';

        $filter = new ScriptFilter($string);

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
        $script = new Script($string, $params, $lang);

        $filter = new ScriptFilter();
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
}
