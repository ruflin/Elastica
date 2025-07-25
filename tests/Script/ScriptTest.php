<?php

declare(strict_types=1);

namespace Elastica\Test\Script;

use Elastica\Exception\InvalidException;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ScriptTest extends BaseTest
{
    private const SCRIPT = "_score * doc['my_numeric_field'].value";

    #[Group('unit')]
    public function testConstructor(): void
    {
        $script = new Script(self::SCRIPT);

        $expected = ['script' => [
            'source' => self::SCRIPT,
        ]];
        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($expected, $script->toArray());

        $params = [
            'param1' => 'one',
            'param2' => 10,
        ];

        $script = new Script(self::SCRIPT, $params);

        $expected = ['script' => [
            'source' => self::SCRIPT,
            'params' => $params,
        ]];

        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals($expected, $script->toArray());

        $script = new Script(self::SCRIPT, $params, Script::LANG_PAINLESS);

        $expected = ['script' => [
            'source' => self::SCRIPT,
            'params' => $params,
            'lang' => Script::LANG_PAINLESS,
        ]];

        $this->assertEquals($expected, $script->toArray());
        $this->assertEquals(self::SCRIPT, $script->getScript());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals(Script::LANG_PAINLESS, $script->getLang());
    }

    #[Group('unit')]
    public function testCreateString(): void
    {
        $script = Script::create(self::SCRIPT);

        $this->assertInstanceOf(Script::class, $script);

        $this->assertEquals(self::SCRIPT, $script->getScript());

        $expected = ['script' => [
            'source' => self::SCRIPT,
        ]];
        $this->assertEquals($expected, $script->toArray());
    }

    #[Group('unit')]
    public function testCreateScript(): void
    {
        $data = new Script(self::SCRIPT);

        $script = Script::create($data);

        $this->assertInstanceOf(Script::class, $script);
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
                'source' => self::SCRIPT,
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

    #[DataProvider('dataProviderCreateInvalid')]
    #[Group('unit')]
    public function testCreateInvalid($data): void
    {
        $this->expectException(InvalidException::class);

        Script::create($data);
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
        $script = new Script(self::SCRIPT, [], Script::LANG_PAINLESS);

        $this->assertSame($script, $script->setLang(Script::LANG_PAINLESS));
        $this->assertEquals(Script::LANG_PAINLESS, $script->getLang());
    }

    #[Group('unit')]
    public function testSetScript(): void
    {
        $script = new Script(self::SCRIPT);

        $this->assertSame($script, $script->setScript('bar'));
        $this->assertEquals('bar', $script->getScript());
    }
}
