<?php
namespace Elastica\Test\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 */
class ProcessingBuilderTest extends BaseTest
{
    /**
     * @var ResultSet\ProcessingBuilder
     */
    private $builder;

    /**
     * @var BuilderInterface
     */
    private $innerBuilder;

    /**
     * @var ResultSet\ProcessorInterface
     */
    private $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->innerBuilder = $this->getMock('Elastica\\ResultSet\\BuilderInterface');
        $this->processor = $this->getMock('Elastica\\ResultSet\\ProcessorInterface');

        $this->builder = new ResultSet\ProcessingBuilder($this->innerBuilder, $this->processor);
    }

    public function testProcessors()
    {
        $response = new Response('');
        $query = new Query();
        $resultSet = new ResultSet($response, $query, []);

        $this->innerBuilder->expects($this->once())
            ->method('buildResultSet')
            ->with($response, $query)
            ->willReturn($resultSet);
        $this->processor->expects($this->once())
            ->method('process')
            ->with($resultSet);

        $this->builder->buildResultSet($response, $query);
    }
}
