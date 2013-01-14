<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\AbstractMultiFilter;
use Elastica\Filter\MatchAllFilter;
use Elastica\Test\Base as BaseTest;

class Abstract_MultiTest extends BaseTest
{
    public function testConstruct()
    {
        $stub = $this->getStub();

        $this->assertEmpty($stub->getFilters());
    }

    public function testAddFilter()
    {
        $stub = $this->getStub();

        $filter = new MatchAllFilter();
        $stub->addFilter($filter);

        $expected = array(
            $filter->toArray()
        );

        $this->assertEquals($expected, $stub->getFilters());
    }

    public function testSetFilters()
    {
        $stub = $this->getStub();

        $filter = new MatchAllFilter();
        $stub->setFilters(array($filter));

        $expected = array(
            $filter->toArray()
        );

        $this->assertEquals($expected, $stub->getFilters());
    }

    public function testToArray()
    {
        $stub = $this->getStub();

        $filter = new MatchAllFilter();
        $stub->addFilter($filter);

        $expected = array(
            $stub->getBaseName() => array(
                $filter->toArray()
            )
        );

        $this->assertEquals($expected, $stub->toArray());
    }

    public function testToArrayWithParam()
    {
        $stub = $this->getStub();

        $stub->setCached(true);

        $filter = new MatchAllFilter();
        $stub->addFilter($filter);

        $expected = array(
            $stub->getBaseName() => array(
                '_cache' => true,
                'filters' => array(
                    $filter->toArray()
                )
            )
        );

        $this->assertEquals($expected, $stub->toArray());
    }

    private function getStub()
    {
        return $this->getMockForAbstractClass('Elastica\Test\Filter\Abstract_MultiDebug');
    }
}

class Abstract_MultiDebug extends AbstractMultiFilter
{
    public function getFilters()
    {
        return $this->_filters;
    }

    public function getBaseName()
    {
        return parent::_getBaseName();
    }
}
