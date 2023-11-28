<?php

namespace Elastica\Test\ResultSet;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Query;
use Elastica\ResultSet\DefaultBuilder;
use Elastica\Test\Base as BaseTest;
use GuzzleHttp\Psr7\Response;

/**
 * @group unit
 *
 * @internal
 */
class BuilderTest extends BaseTest
{
    /**
     * @var DefaultBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new DefaultBuilder();
    }

    public function testEmptyResponse(): void
    {
        $response = new Elasticsearch();
        $response->setResponse(new Response(
            200,
            [
                Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            \json_encode([])
        ));
        $query = new Query();

        $resultSet = $this->builder->buildResultSet($response, $query);

        $this->assertSame($response, $resultSet->getResponse());
        $this->assertSame($query, $resultSet->getQuery());
        $this->assertCount(0, $resultSet->getResults());
    }

    public function testResponse(): void
    {
        $response = new Elasticsearch();
        $response->setResponse(new Response(
            200,
            [
                Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            \json_encode([
                'hits' => [
                    'hits' => [
                        ['test' => 1],
                        ['test' => 2],
                        ['test' => 3],
                    ],
                ],
            ])
        ));
        $query = new Query();

        $resultSet = $this->builder->buildResultSet($response, $query);

        $this->assertSame($response, $resultSet->getResponse());
        $this->assertSame($query, $resultSet->getQuery());
        $this->assertCount(3, $resultSet->getResults());
    }
}
