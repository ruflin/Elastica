<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Script as ScriptFilter;
use Elastica\Script\Script;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class ScriptTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new ScriptFilter());
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $string = '_score * 2.0';

        $filter = new ScriptFilter($string);

        $array = $filter->toArray();
        $this->assertInternalType('array', $array);

        $expected = array(
            'script' => array(
                'script' => $string,
            ),
        );
        $this->assertEquals($expected, $array);
    }

    /**
     * @group unit
     */
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
            ),
        );
        $this->assertEquals($expected, $array);
    }
}
