<?php

namespace Elastica\Test\ResultSet;

use Elastica\Event\ElasticaEvents;
use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet\Builder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group unit
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    protected function setUp()
    {
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->builder = new Builder($this->dispatcher);
    }

    public function testEmptyResponse()
    {
        $response = new Response('');
        $query = new Query();

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                ElasticaEvents::BUILD_RESULT_SET,
                $this->isInstanceOf('Elastica\Event\ResultSetEvent')
            );

        $resultSet = $this->builder->buildResultSet($response, $query);

        $this->assertSame($response, $resultSet->getResponse());
        $this->assertSame($query, $resultSet->getQuery());
        $this->assertCount(0, $resultSet->getResults());
    }

    public function testResponse()
    {
        $response = new Response([
            'hits' => [
                'hits' => [
                    ['test' => 1],
                    ['test' => 2],
                    ['test' => 3],
                ]
            ]
        ]);
        $query = new Query();

        $this->dispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [ElasticaEvents::BUILD_RESULT, $this->isInstanceOf('Elastica\Event\ResultEvent')],
                [ElasticaEvents::BUILD_RESULT, $this->isInstanceOf('Elastica\Event\ResultEvent')],
                [ElasticaEvents::BUILD_RESULT, $this->isInstanceOf('Elastica\Event\ResultEvent')],
                [ElasticaEvents::BUILD_RESULT_SET, $this->isInstanceOf('Elastica\Event\ResultSetEvent')]
            );

        $resultSet = $this->builder->buildResultSet($response, $query);

        $this->assertSame($response, $resultSet->getResponse());
        $this->assertSame($query, $resultSet->getQuery());
        $this->assertCount(3, $resultSet->getResults());
    }
}
