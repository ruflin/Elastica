<?php
namespace Elastica\Test\Multi;

use Elastica\Multi\MultiBuilder;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 */
class MultiBuilderTest extends BaseTest
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var MultiBuilder
     */
    private $multiBuilder;

    protected function setUp()
    {
        parent::setUp();

        $this->builder = $this->getMock('Elastica\\ResultSet\\BuilderInterface');
        $this->multiBuilder = new MultiBuilder($this->builder);
    }

    public function testBuildEmptyMultiResultSet()
    {
        $this->builder->expects($this->never())
            ->method('buildResultSet');

        $response = new Response(array());
        $searches = array();

        $result = $this->multiBuilder->buildMultiResultSet($response, $searches);

        $this->assertInstanceOf('Elastica\\Multi\\ResultSet', $result);
    }

    public function testBuildMultiResultSet()
    {
        $response = new Response(array(
            'responses' => array(
                array(),
                array(),
            ),
        ));
        $searches = array(
            $s1 = new Search($this->_getClient(), $this->builder),
            $s2 = new Search($this->_getClient(), $this->builder),
        );
        $resultSet1 = new ResultSet(new Response(array()), $s1->getQuery(), array());
        $resultSet2 = new ResultSet(new Response(array()), $s2->getQuery(), array());

        $this->builder->expects($this->exactly(2))
            ->method('buildResultSet')
            ->withConsecutive(
                array($this->isInstanceOf('Elastica\\Response'), $s1->getQuery()),
                array($this->isInstanceOf('Elastica\\Response'), $s2->getQuery())
            )
            ->willReturnOnConsecutiveCalls($resultSet1, $resultSet2);

        $result = $this->multiBuilder->buildMultiResultSet($response, $searches);

        $this->assertInstanceOf('Elastica\\Multi\\ResultSet', $result);
        $this->assertSame($resultSet1, $result[0]);
        $this->assertSame($resultSet2, $result[1]);
    }
}
