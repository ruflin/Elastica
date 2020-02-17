<?php

namespace Elastica\Test;

use Elastica\Param;
use Elastica\Test\Base as BaseTest;
use Elastica\Util;

/**
 * @internal
 */
class ParamTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArrayEmpty(): void
    {
        $param = new Param();
        $this->assertInstanceOf(Param::class, $param);
        $this->assertEquals([$this->_getFilterName($param) => []], $param->toArray());
    }

    /**
     * @group unit
     */
    public function testSetParams(): void
    {
        $param = new Param();
        $params = ['hello' => 'word', 'nicolas' => 'ruflin'];
        $param->setParams($params);

        $this->assertInstanceOf(Param::class, $param);
        $this->assertEquals([$this->_getFilterName($param) => $params], $param->toArray());
    }

    /**
     * @group unit
     */
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

    /**
     * @group unit
     */
    public function testAddParam(): void
    {
        $param = new Param();

        $key = 'name';
        $value = 'nicolas ruflin';

        $param->addParam($key, $value);

        $this->assertEquals([$key => [$value]], $param->getParams());
        $this->assertEquals([$value], $param->getParam($key));
    }

    /**
     * @group unit
     */
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

    /**
     * @group unit
     */
    public function testGetParamInvalid(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $param = new Param();

        $param->getParam('notest');
    }

    /**
     * @group unit
     */
    public function testHasParam(): void
    {
        $param = new Param();

        $key = 'name';
        $value = 'nicolas ruflin';

        $this->assertFalse($param->hasParam($key));

        $param->setParam($key, $value);
        $this->assertTrue($param->hasParam($key));
    }

    protected function _getFilterName($filter)
    {
        return Util::getParamName($filter);
    }
}
