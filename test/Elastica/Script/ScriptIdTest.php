<?php

namespace Elastica\Test;

use Elastica\Script\ScriptId;
use Elastica\Test\Base as BaseTest;

class ScriptIdTest extends BaseTest
{
    const SCRIPT_ID = 'my_script';

    /**
     * @group unit
     */
    public function testConstructor()
    {
        $script = new ScriptId(self::SCRIPT_ID);

        $expected = ['script' => [
            'id' => self::SCRIPT_ID,
        ]];
        $this->assertEquals(self::SCRIPT_ID, $script->getScriptId());
        $this->assertEquals($expected, $script->toArray());

        $params = [
            'param1' => 'one',
            'param2' => 10,
        ];

        $script = new ScriptId(self::SCRIPT_ID, $params);

        $expected = ['script' => [
            'id' => self::SCRIPT_ID,
            'params' => $params,
        ]];

        $this->assertEquals(self::SCRIPT_ID, $script->getScriptId());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($expected, $script->toArray());

        $script = new ScriptId(self::SCRIPT_ID, $params, ScriptId::LANG_PAINLESS);

        $expected = ['script' => [
            'id' => self::SCRIPT_ID,
            'params' => $params,
            'lang' => ScriptId::LANG_PAINLESS,
        ]];

        $this->assertEquals($expected, $script->toArray());
        $this->assertEquals(self::SCRIPT_ID, $script->getScriptId());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals(ScriptId::LANG_PAINLESS, $script->getLang());
    }

    /**
     * @group unit
     */
    public function testCreateString()
    {
        $script = ScriptId::create(self::SCRIPT_ID);

        $this->assertInstanceOf(ScriptId::class, $script);

        $this->assertEquals(self::SCRIPT_ID, $script->getScriptId());

        $expected = ['script' => [
            'id' => self::SCRIPT_ID,
        ]];
        $this->assertEquals($expected, $script->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateScript()
    {
        $data = new ScriptId(self::SCRIPT_ID);

        $script = ScriptId::create($data);

        $this->assertInstanceOf(ScriptId::class, $script);
        $this->assertSame($data, $script);
    }

    /**
     * @group unit
     */
    public function testCreateArray()
    {
        $params = [
            'param1' => 'one',
            'param2' => 1,
        ];
        $array = [
            'script' => [
                'id' => self::SCRIPT_ID,
                'lang' => ScriptId::LANG_PAINLESS,
                'params' => $params,
            ],
        ];

        $script = ScriptId::create($array);

        $this->assertInstanceOf(ScriptId::class, $script);
        $this->assertEquals($array, $script->toArray());

        $this->assertEquals(self::SCRIPT_ID, $script->getScriptId());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals(ScriptId::LANG_PAINLESS, $script->getLang());
    }

    /**
     * @group unit
     * @dataProvider dataProviderCreateInvalid
     */
    public function testCreateInvalid($data)
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        ScriptId::create($data);
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
        $script = new ScriptId(self::SCRIPT_ID, [], ScriptId::LANG_PAINLESS);

        $this->assertSame($script, $script->setLang(ScriptId::LANG_PAINLESS));
        $this->assertEquals(ScriptId::LANG_PAINLESS, $script->getLang());
    }

    /**
     * @group unit
     */
    public function testSetScriptId()
    {
        $script = new ScriptId(self::SCRIPT_ID);

        $this->assertSame($script, $script->setScriptId('other_script'));
        $this->assertEquals('other_script', $script->getScriptId());
    }
}
