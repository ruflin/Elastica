<?php

namespace Elastica\Test\Filter;

use Elastica\Test\Base as BaseTest;

class AbstractTest extends BaseTest
{
    public function testSetCached()
    {
        $stubFilter = $this->getStub();

        $stubFilter->setCached(true);
        $arrayFilter = current($stubFilter->toArray());
        $this->assertTrue($arrayFilter['_cache']);

        $stubFilter->setCached(false);
        $arrayFilter = current($stubFilter->toArray());
        $this->assertFalse($arrayFilter['_cache']);
    }

    public function testSetCachedDefaultValue()
    {
        $stubFilter = $this->getStub();

        $stubFilter->setCached();
        $arrayFilter = current($stubFilter->toArray());
        $this->assertTrue($arrayFilter['_cache']);
    }

    public function testSetCacheKey()
    {
        $stubFilter = $this->getStub();

        $cacheKey = 'myCacheKey';

        $stubFilter->setCacheKey($cacheKey);
        $arrayFilter = current($stubFilter->toArray());
        $this->assertEquals($cacheKey, $arrayFilter['_cache_key']);
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testSetCacheKeyEmptyKey()
    {
        $stubFilter = $this->getStub();

        $cacheKey = '';

        $stubFilter->setCacheKey($cacheKey);
    }

    public function testSetName()
    {
        $stubFilter = $this->getStub();

        $name = 'myFilter';

        $stubFilter->setName($name);
        $arrayFilter = current($stubFilter->toArray());
        $this->assertEquals($name, $arrayFilter['_name']);
    }

    private function getStub()
    {
        return $this->getMockForAbstractClass('Elastica\Filter\AbstractFilter');
    }
}
