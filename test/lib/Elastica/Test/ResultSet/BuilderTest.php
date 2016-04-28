<?php

namespace Elastica\Test\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet\Builder;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 */
class BuilderTest extends BaseTest
{
    /**
     * @var Builder
     */
    private $builder;

    protected function setUp()
    {
        parent::setUp();

        $this->builder = new Builder();
    }

    public function testEmptyResponse()
    {
        $response = new Response('');
        $query = new Query();

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

        $resultSet = $this->builder->buildResultSet($response, $query);

        $this->assertSame($response, $resultSet->getResponse());
        $this->assertSame($query, $resultSet->getQuery());
        $this->assertCount(3, $resultSet->getResults());
    }
}
