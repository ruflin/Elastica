<?php
namespace Elastica\Test;

use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;

class ScriptTest extends BaseTest
{
    const SCRIPT = "_score * doc['my_numeric_field'].value";
    /**
     * @group unit
     */
    public function testConstructor()
    {
        $script = new Script(self::SCRIPT);

        $expected = ['script' => [
            'inline' => self::SCRIPT,
        ]];
        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($expected, $script->toArray());

        $params = [
            'param1' => 'one',
            'param2' => 10,
        ];

        $script = new Script(self::SCRIPT, $params);

        $expected = ['script' => [
            'inline' => self::SCRIPT,
            'params' => $params,
        ]];

        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($expected, $script->toArray());

        $script = new Script(self::SCRIPT, $params, Script::LANG_PAINLESS);

        $expected = ['script' => [
            'inline' => self::SCRIPT,
            'params' => $params,
            'lang' => Script::LANG_PAINLESS,
        ]];

        $this->assertEquals($expected, $script->toArray());
        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals(Script::LANG_PAINLESS, $script->getLang());
    }

    /**
     * @group unit
     */
    public function testCreateString()
    {
        $script = Script::create(self::SCRIPT);

        $this->assertInstanceOf(Script::class, $script);

        $this->assertEquals(self::SCRIPT, $script->getScript());

        $expected = ['script' => [
            'inline' => self::SCRIPT,
        ]];
        $this->assertEquals($expected, $script->toArray());
    }

    /**
     * @group unit
     */
    public function testCreateScript()
    {
        $data = new Script(self::SCRIPT);

        $script = Script::create($data);

        $this->assertInstanceOf(Script::class, $script);
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
                'inline' => self::SCRIPT,
                'lang' => Script::LANG_PAINLESS,
                'params' => $params,
            ],
        ];

        $script = Script::create($array);

        $this->assertInstanceOf(Script::class, $script);
        $this->assertEquals($array, $script->toArray());

        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals(Script::LANG_PAINLESS, $script->getLang());
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
        $script = new Script(self::SCRIPT, [], Script::LANG_PAINLESS);

        $this->assertSame($script, $script->setLang(Script::LANG_GROOVY));
        $this->assertEquals(Script::LANG_GROOVY, $script->getLang());
    }

    /**
     * @group unit
     */
    public function testSetScript()
    {
        $script = new Script(self::SCRIPT);

        $this->assertSame($script, $script->setScript('bar'));
        $this->assertEquals('bar', $script->getScript());
    }
}
