<?php

namespace Elastica\Test\Multi;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Multi\MultiBuilder;
use Elastica\ResultSet;
use Elastica\ResultSet\BuilderInterface;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group unit
 *
 * @internal
 */
class MultiBuilderTest extends BaseTest
{
    /**
     * @var BuilderInterface&MockObject
     */
    private $builder;

    /**
     * @var MultiBuilder
     */
    private $multiBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = $this->createMock(BuilderInterface::class);
        $this->multiBuilder = new MultiBuilder();
    }

    public function testBuildEmptyMultiResultSet(): void
    {
        $this->builder->expects($this->never())
            ->method('buildResultSet')
        ;

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

        $searches = [];

        $result = $this->multiBuilder->buildMultiResultSet($response, $searches);

        $this->assertCount(0, $result->getResultSets());
    }

    public function testBuildMultiResultSet(): void
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
                'responses' => [
                    [],
                    [],
                ],
            ])
        ));

        $searches = [
            $s1 = new Search($this->_getClient(), $this->builder),
            $s2 = new Search($this->_getClient(), $this->builder),
        ];

        $responseResultSet1 = new Elasticsearch();
        $responseResultSet1->setResponse(new Response(
            200,
            [
                Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            \json_encode([])
        ));

        $responseResultSet2 = new Elasticsearch();
        $responseResultSet2->setResponse(new Response(
            200,
            [
                Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            \json_encode([])
        ));

        $resultSet1 = new ResultSet($responseResultSet1, $s1->getQuery(), []);
        $resultSet2 = new ResultSet($responseResultSet2, $s2->getQuery(), []);

        $this->builder->expects($this->exactly(2))
            ->method('buildResultSet')
            ->withConsecutive(
                [$this->isInstanceOf(Elasticsearch::class), $s1->getQuery()],
                [$this->isInstanceOf(Elasticsearch::class), $s2->getQuery()]
            )
            ->willReturnOnConsecutiveCalls($resultSet1, $resultSet2)
        ;

        $result = $this->multiBuilder->buildMultiResultSet($response, $searches);

        $this->assertSame($resultSet1, $result[0]);
        $this->assertSame($resultSet2, $result[1]);
    }
}
