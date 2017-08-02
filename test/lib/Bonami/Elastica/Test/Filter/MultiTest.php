<?php
namespace Elastica\Test\Filter;

use Elastica\Filter\AbstractMulti;
use Elastica\Filter\MatchAll;
use Elastica\Test\Base as BaseTest;

class AbstractMultiTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $stub = $this->getStub();

        $this->assertEmpty($stub->getFilters());
    }

    /**
     * @group unit
     */
    public function testAddFilter()
    {
        $stub = $this->getStub();

        $filter = new MatchAll();
        $stub->addFilter($filter);

        $expected = array(
            $filter,
        );

        $this->assertSame($expected, $stub->getFilters());
    }

    /**
     * @group unit
     */
    public function testSetFilters()
    {
        $stub = $this->getStub();

        $filter = new MatchAll();
        $stub->setFilters(array($filter));

        $expected = array(
            $filter,
        );

        $this->assertSame($expected, $stub->getFilters());
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $stub = $this->getStub();

        $filter = new MatchAll();
        $stub->addFilter($filter);

        $expected = array(
            $stub->getBaseName() => array(
                $filter->toArray(),
            ),
        );

        $this->assertEquals($expected, $stub->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayWithParam()
    {
        $stub = $this->getStub();

        $stub->setCached(true);

        $filter = new MatchAll();
        $stub->addFilter($filter);

        $expected = array(
            $stub->getBaseName() => array(
                '_cache' => true,
                'filters' => array(
                    $filter->toArray(),
                ),
            ),
        );

        $this->assertEquals($expected, $stub->toArray());
    }

    private function getStub()
    {
        return $this->getMockForAbstractClass('Elastica\Test\Filter\AbstractMultiDebug');
    }
}

class AbstractMultiDebug extends AbstractMulti
{
    public function getBaseName()
    {
        return parent::_getBaseName();
    }
}
