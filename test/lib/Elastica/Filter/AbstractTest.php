<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_AbstractTest extends PHPUnit_Framework_TestCase
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
     * @expectedException Elastica_Exception_Invalid
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
        return $this->getMockForAbstractClass('Elastica_Filter_Abstract');
    }

    public function testOrFilter()
    {
        $filter_lhs = $this->getStub();
        $filter_lhs->setName('lhs');

        $filter_rhs = $this->getStub();
        $filter_rhs->setName('rhs');

        $orFilter = $filter_lhs->orFilter($filter_rhs);

        $this->assertInstanceOf('Elastica_Filter_Or', $orFilter);

        $expectedArray = array(
            'or' => array(
                    $filter_lhs->toArray(),
                    $filter_rhs->toArray()
                )
            );

        $this->assertEquals($expectedArray, $orFilter->toArray(), 'lhs.||(rhs) = lhs || rhs');

        $filter_more = $this->getStub();
        $filter_more->setName('more');

        $returnValue = $orFilter->orFilter($filter_more);

        $this->assertSame($orFilter, $returnValue);

        $expectedArray = array(
            'or' => array(
                    $filter_lhs->toArray(),
                    $filter_rhs->toArray(),
                    $filter_more->toArray(),
                )
            );

        $this->assertEquals($expectedArray, $returnValue->toArray(), '(lhs || rhs) || more = (lhs || rhs || more)');

        $match_all = new Elastica_Filter_MatchAll;
        $returnValue = $match_all->orFilter($filter_rhs);
        $this->assertSame($returnValue, $filter_rhs, 'all || filter = filter');
    }

    public function testAndFilter()
    {
        $filter_lhs = $this->getStub();
        $filter_lhs->setName('lhs');

        $filter_rhs = $this->getStub();
        $filter_rhs->setName('rhs');

        $andFilter = $filter_lhs->andFilter($filter_rhs);

        $this->assertInstanceOf('Elastica_Filter_And', $andFilter);

        $expectedArray = array(
            'and' => array(
                    $filter_lhs->toArray(),
                    $filter_rhs->toArray()
                )
            );

        $this->assertEquals($expectedArray, $andFilter->toArray(), 'lhs.&&(rhs) = lhs && rhs');

        $filter_more = $this->getStub();
        $filter_more->setName('more');

        $returnValue = $andFilter->andFilter($filter_more);

        $this->assertSame($andFilter, $returnValue);

        $expectedArray = array(
            'and' => array(
                    $filter_lhs->toArray(),
                    $filter_rhs->toArray(),
                    $filter_more->toArray(),
                )
            );

        $this->assertEquals($expectedArray, $returnValue->toArray(), '(lhs && rhs) && more = (lhs && rhs && more)');

        $match_all = new Elastica_Filter_MatchAll;
        $returnValue = $match_all->andFilter($filter_rhs);

        $this->assertSame($returnValue, $filter_rhs, 'all && filter = filter');
    }
}
