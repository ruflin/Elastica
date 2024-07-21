<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Exception\InvalidException;
use Elastica\Param;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ParamTest extends BaseTest
{
    #[Group('unit')]
    public function testToArrayEmpty(): void
    {
        $param = new Param();
        $this->assertInstanceOf(Param::class, $param);
        $this->assertEquals(['param' => []], $param->toArray());
    }

    #[Group('unit')]
    public function testSetParams(): void
    {
        $param = new Param();
        $params = ['hello' => 'word', 'nicolas' => 'ruflin'];
        $param->setParams($params);

        $this->assertInstanceOf(Param::class, $param);
        $this->assertEquals(['param' => $params], $param->toArray());
    }

    #[Group('unit')]
    public function testSetGetParam(): void
    {
        $param = new Param();

        $key = 'name';
        $value = 'nicolas ruflin';

        $params = [$key => $value];
        $param->setParam($key, $value);

        $this->assertEquals($params, $param->getParams());
        $this->assertEquals($value, $param->getParam($key));
    }

    #[Group('unit')]
    public function testAddParam(): void
    {
        $param = new Param();

        $key = 'name';
        $value = 'nicolas ruflin';

        $param->addParam($key, $value);

        $this->assertEquals([$key => [$value]], $param->getParams());
        $this->assertEquals([$value], $param->getParam($key));
    }

    #[Group('unit')]
    public function testAddParam2(): void
    {
        $param = new Param();

        $key = 'name';
        $value1 = 'nicolas';
        $value2 = 'ruflin';

        $param->addParam($key, $value1);
        $param->addParam($key, $value2);

        $this->assertEquals([$key => [$value1, $value2]], $param->getParams());
        $this->assertEquals([$value1, $value2], $param->getParam($key));
    }

    #[Group('unit')]
    public function testGetParamInvalid(): void
    {
        $this->expectException(InvalidException::class);

        $param = new Param();

        $param->getParam('notest');
    }

    #[Group('unit')]
    public function testHasParam(): void
    {
        $param = new Param();

        $key = 'name';
        $value = 'nicolas ruflin';

        $this->assertFalse($param->hasParam($key));

        $param->setParam($key, $value);
        $this->assertTrue($param->hasParam($key));
    }
}
