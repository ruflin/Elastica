<?php

declare(strict_types=1);

namespace Elastica\Test\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\ResultSet\BuilderInterface;
use Elastica\ResultSet\ProcessingBuilder;
use Elastica\ResultSet\ProcessorInterface;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
#[Group('unit')]
class ProcessingBuilderTest extends BaseTest
{
    /**
     * @var ProcessingBuilder
     */
    private $builder;

    /**
     * @var BuilderInterface|MockObject
     */
    private $innerBuilder;

    /**
     * @var MockObject|ProcessorInterface
     */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->innerBuilder = $this->createMock(BuilderInterface::class);
        $this->processor = $this->createMock(ProcessorInterface::class);

        $this->builder = new ProcessingBuilder($this->innerBuilder, $this->processor);
    }

    public function testProcessors(): void
    {
        $response = new Response('');
        $query = new Query();
        $resultSet = new ResultSet($response, $query, []);

        $this->innerBuilder->expects($this->once())
            ->method('buildResultSet')
            ->with($response, $query)
            ->willReturn($resultSet)
        ;
        $this->processor->expects($this->once())
            ->method('process')
            ->with($resultSet)
        ;

        $this->builder->buildResultSet($response, $query);
    }
}
