<?php

namespace Elastica\Test;

use Elastica\Script;
use Elastica\Test\Base as BaseTest;

class ScriptTest extends BaseTest
{
    public function testConstructor()
    {
        $value = "_score * doc['my_numeric_field'].value";
        $script = new Script($value);

        $expected = array(
            'script' => $value,
        );
        $this->assertEquals($value, $script->getScript());
        $this->assertEquals($expected, $script->toArray());

        $params = array(
            'param1' => 'one',
            'param2' => 10,
        );

        $script = new Script($value, $params);

        $expected = array(
            'script' => $value,
            'params' => $params,
        );

        $this->assertEquals($value, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($expected, $script->toArray());

        $lang = 'mvel';

        $script = new Script($value, $params, $lang);

        $expected = array(
            'script' => $value,
            'params' => $params,
            'lang' => $lang,
        );

        $this->assertEquals($value, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($lang, $script->getLang());
        $this->assertEquals($expected, $script->toArray());
    }

    public function testCreateString()
    {
        $string = '_score * 2.0';
        $script = Script::create($string);

        $this->assertInstanceOf('Elastica\Script', $script);

        $this->assertEquals($string, $script->getScript());

        $expected = array(
            'script' => $string,
        );
        $this->assertEquals($expected, $script->toArray());
    }

    public function testCreateScript()
    {
        $data = new Script('_score * 2.0');

        $script = Script::create($data);

        $this->assertInstanceOf('Elastica\Script', $script);
        $this->assertSame($data, $script);
    }

    public function testCreateArray()
    {
        $string = '_score * 2.0';
        $lang = 'mvel';
        $params = array(
            'param1' => 'one',
            'param2' => 1,
        );
        $array = array(
            'script' => $string,
            'lang' => $lang,
            'params' => $params,
        );

        $script = Script::create($array);

        $this->assertInstanceOf('Elastica\Script', $script);

        $this->assertEquals($string, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($lang, $script->getLang());

        $this->assertEquals($array, $script->toArray());
    }

    /**
     * @dataProvider dataProviderCreateInvalid
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testCreateInvalid($data)
    {
        Script::create($data);
    }

    /**
     * @return array
     */
    public function dataProviderCreateInvalid()
    {
        return array(
            array(
                new \stdClass(),
            ),
            array(
                array('params' => array('param1' => 'one')),
            ),
            array(
                array('script' => '_score * 2.0', 'params' => 'param'),
            ),
        );
    }
}
