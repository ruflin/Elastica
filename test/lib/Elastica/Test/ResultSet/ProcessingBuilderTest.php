<?php
namespace Elastica\Test\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\BuilderInterface;
use Elastica\ResultSet\ProcessingBuilder;
use Elastica\ResultSet\ProcessorInterface;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 */
class ProcessingBuilderTest extends BaseTest
{
    /**
     * @var ProcessingBuilder
     */
    private $builder;

    /**
     * @var BuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $innerBuilder;

    /**
     * @var ProcessorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->innerBuilder = $this->createMock(BuilderInterface::class);
        $this->processor = $this->createMock(ProcessorInterface::class);

        $this->builder = new ProcessingBuilder($this->innerBuilder, $this->processor);
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
