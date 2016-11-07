<?php
namespace Elastica\Test;

use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;

class ScriptTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstructor()
    {
        $value = "_score * doc['my_numeric_field'].value";
        $script = new Script($value);

        $expected = ['script' => [
            'inline' => $value,
        ]];
        $this->assertEquals($value, $script->getScript());
        $this->assertEquals($expected, $script->toArray());

        $params = [
            'param1' => 'one',
            'param2' => 10,
        ];

        $script = new Script($value, $params);

        $expected = ['script' => [
            'inline' => $value,
            'params' => $params,
        ]];

        $this->assertEquals($value, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($expected, $script->toArray());

        $lang = 'mvel';

        $script = new Script($value, $params, $lang);

        $expected = ['script' => [
            'inline' => $value,
            'params' => $params,
            'lang' => $lang,
        ]];

        $this->assertEquals($expected, $script->toArray());
        $this->assertEquals($value, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($lang, $script->getLang());
    }

    /**
     * @group unit
     */
    public function testCreateString()
    {
        $string = '_score * 2.0';
        $script = Script::create($string);

        $this->assertInstanceOf('Elastica\Script\Script', $script);

        $this->assertEquals($string, $script->getScript());

        $expected = ['script' => [
            'inline' => $string,
        ]];
        $this->assertEquals($expected, $script->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateScript()
    {
        $data = new Script('_score * 2.0');

        $script = Script::create($data);

        $this->assertInstanceOf('Elastica\Script\Script', $script);
        $this->assertSame($data, $script);
    }

    /**
     * @group unit
     */
    public function testCreateArray()
    {
        $string = '_score * 2.0';
        $lang = 'mvel';
        $params = [
            'param1' => 'one',
            'param2' => 1,
        ];
        $array = [
            'inline' => $string,
            'lang' => $lang,
            'params' => $params,
        ];

        $script = Script::create($array);

        $this->assertInstanceOf('Elastica\Script\Script', $script);
        $this->assertEquals(['script' => $array], $script->toArray());

        $this->assertEquals($string, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($lang, $script->getLang());
    }

    /**
     * @group unit
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
        return [
            [
                new \stdClass(),
            ],
            [
                ['params' => ['param1' => 'one']],
            ],
            [
                ['script' => '_score * 2.0', 'params' => 'param'],
            ],
        ];
    }

    /**
     * @group unit
     */
    public function testSetLang()
    {
        $script = new Script('foo', [], Script::LANG_PAINLESS);
        $this->assertEquals(Script::LANG_PAINLESS, $script->getLang());

        $script->setLang(Script::LANG_GROOVY);
        $this->assertEquals(Script::LANG_GROOVY, $script->getLang());

        $this->assertInstanceOf('Elastica\Script\Script', $script->setLang(Script::LANG_GROOVY));
    }

    /**
     * @group unit
     */
    public function testSetScript()
    {
        $script = new Script('foo');
        $this->assertEquals('foo', $script->getScript());

        $script->setScript('bar');
        $this->assertEquals('bar', $script->getScript());

        $this->assertInstanceOf('Elastica\Script\Script', $script->setScript('foo'));
    }
}
