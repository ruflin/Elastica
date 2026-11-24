<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Client;
use Elastica\Query\MatchQuery;
use Elastica\Result;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Executable examples for docs/testing.md.
 *
 * @internal
 */
class ClientMockingTest extends Base
{
    #[Group('unit')]
    #[DataProvider('provideSearchResults')]
    public function testSearchWithMockedResponse(array $hits, array $expectedTitles): void
    {
        $response = new Elasticsearch();
        $response->setResponse(new Psr7Response(200, [
            'Content-Type' => 'application/json',
            Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
        ], \json_encode([
            'took' => 1,
            'timed_out' => false,
            '_shards' => ['total' => 1, 'successful' => 1, 'skipped' => 0, 'failed' => 0],
            'hits' => [
                'total' => ['value' => \count($hits), 'relation' => 'eq'],
                'max_score' => $hits ? 1.0 : null,
                'hits' => $hits,
            ],
        ], \JSON_THROW_ON_ERROR)));

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['search'])
            ->getMock()
        ;
        $client->expects($this->once())->method('search')
            ->with(['index' => 'books', 'body' => ['query' => ['match' => ['title' => 'Elastica']]]])
            ->willReturn($response)
        ;

        $results = $client->getIndex('books')->search(new MatchQuery('title', 'Elastica'));

        $this->assertSame(\count($hits), $results->getTotalHits());
        $this->assertSame($expectedTitles, \array_map(
            static fn (Result $result): string => $result->getData()['title'],
            $results->getResults()
        ));
    }

    public static function provideSearchResults(): iterable
    {
        yield 'one hit' => [[[
            '_index' => 'books',
            '_id' => '42',
            '_score' => 1.0,
            '_source' => ['title' => 'Elastica guide'],
        ]], ['Elastica guide']];

        yield 'no hits' => [[], []];
    }
}
