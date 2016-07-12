<?php
namespace Elastica\Test\Query;

use Elastica\Query\Script as ScriptQuery;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;

class ScriptTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $string = '_score * 2.0';

        $query = new ScriptQuery($string);

        $array = $query->toArray();
        $this->assertInternalType('array', $array);

        $expected = [
            'script' => [
                'script' => $string,
            ],
        ];
        $this->assertEquals($expected, $array);
    }

    /**
     * @group unit
     */
    public function testSetScript()
    {
        $string = '_score * 2.0';
        $params = [
            'param1' => 'one',
            'param2' => 1,
        ];
        $lang = 'mvel';
        $script = new Script($string, $params, $lang);

        $query = new ScriptQuery();
        $query->setScript($script);

        $array = $query->toArray();

        $expected = [
            'script' => [
                'script' => $string,
                'params' => $params,
                'lang' => $lang,
            ],
        ];
        $this->assertEquals($expected, $array);
    }
}
