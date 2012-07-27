<?php

require_once dirname(__FILE__) . '/../../../../bootstrap.php';

class Elastica_Filter_Abstract_MultiTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $stub = $this->getStub();

        $this->assertEmpty($stub->getFilters());
    }

    public function testAddFilter()
    {
        $stub = $this->getStub();

        $filter = new Elastica_Filter_MatchAll();
        $stub->addFilter($filter);

        $expected = array(
            $filter->toArray()
        );

        $this->assertEquals($expected, $stub->getFilters());
    }

    public function testSetFilters()
    {
        $stub = $this->getStub();

        $filter = new Elastica_Filter_MatchAll();
        $stub->setFilters(array($filter));

        $expected = array(
            $filter->toArray()
        );

        $this->assertEquals($expected, $stub->getFilters());
    }

    public function testToArray()
    {
        $stub = $this->getStub();

        $filter = new Elastica_Filter_MatchAll();
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

        $filter = new Elastica_Filter_MatchAll();
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
        return $this->getMockForAbstractClass('Elastica_Filter_Abstract_MultiDebug');
    }
}

abstract class Elastica_Filter_Abstract_MultiDebug extends Elastica_Filter_Abstract_Multi
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
