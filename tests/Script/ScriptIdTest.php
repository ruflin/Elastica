<?php

declare(strict_types=1);

namespace Elastica\Test\Script;

use Elastica\Exception\InvalidException;
use Elastica\Script\ScriptId;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ScriptIdTest extends BaseTest
{
    private const SCRIPT_ID = 'my_script';

    #[Group('unit')]
    public function testConstructor(): void
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

    #[Group('unit')]
    public function testCreateString(): void
    {
        $script = ScriptId::create(self::SCRIPT_ID);

        $this->assertInstanceOf(ScriptId::class, $script);

        $this->assertEquals(self::SCRIPT_ID, $script->getScriptId());

        $expected = ['script' => [
            'id' => self::SCRIPT_ID,
        ]];
        $this->assertEquals($expected, $script->toArray());
    }

    #[Group('unit')]
    public function testCreateScript(): void
    {
        $data = new ScriptId(self::SCRIPT_ID);

        $script = ScriptId::create($data);

        $this->assertInstanceOf(ScriptId::class, $script);
        $this->assertSame($data, $script);
    }

    #[Group('unit')]
    public function testCreateArray(): void
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

    #[DataProvider('dataProviderCreateInvalid')]
    #[Group('unit')]
    public function testCreateInvalid($data): void
    {
        $this->expectException(InvalidException::class);

        ScriptId::create($data);
    }

    public static function dataProviderCreateInvalid(): array
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

    #[Group('unit')]
    public function testSetLang(): void
    {
        $script = new ScriptId(self::SCRIPT_ID, [], ScriptId::LANG_PAINLESS);

        $this->assertSame($script, $script->setLang(ScriptId::LANG_PAINLESS));
        $this->assertEquals(ScriptId::LANG_PAINLESS, $script->getLang());
    }

    #[Group('unit')]
    public function testSetScriptId(): void
    {
        $script = new ScriptId(self::SCRIPT_ID);

        $this->assertSame($script, $script->setScriptId('other_script'));
        $this->assertEquals('other_script', $script->getScriptId());
    }
}
