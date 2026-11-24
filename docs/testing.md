# Testing application code without Elasticsearch

For application code that uses existing Elastica queries and endpoints, replace
the client endpoint your code calls and return a real Elasticsearch response.
This keeps Elastica's `Index`, `Search` and `ResultSet` behavior in the test while
avoiding a network request. Inject this client into your application service in
place of its normal client.

The following PHPUnit 10 example targets Elastica 9.x. It uses
`GuzzleHttp\Psr7\Response` to construct the HTTP response; install
`guzzlehttp/psr7` as a development dependency if your application does not already
have it. This repository's development dependencies include it.

```php
<?php

declare(strict_types=1);

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Client;
use Elastica\Query\MatchQuery;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\TestCase;

class SearchResponseTest extends TestCase
{
    public function testSearchResult(): void
    {
        $response = new Elasticsearch();
        $response->setResponse(new Psr7Response(200, [
            'Content-Type' => 'application/json',
            Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
        ], json_encode([
            'took' => 1,
            'timed_out' => false,
            '_shards' => ['total' => 1, 'successful' => 1, 'skipped' => 0, 'failed' => 0],
            'hits' => [
                'total' => ['value' => 1, 'relation' => 'eq'],
                'max_score' => 1.0,
                'hits' => [[
                    '_index' => 'books',
                    '_id' => '42',
                    '_score' => 1.0,
                    '_source' => ['title' => 'Elastica guide'],
                ]],
            ],
        ], JSON_THROW_ON_ERROR)));

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['search'])
            ->getMock();
        $client->expects($this->once())->method('search')
            ->with(['index' => 'books', 'body' => ['query' => ['match' => ['title' => 'Elastica']]]])
            ->willReturn($response);

        $results = $client->getIndex('books')->search(new MatchQuery('title', 'Elastica'));

        $this->assertSame(1, $results->getTotalHits());
        $this->assertSame('42', $results->getResults()[0]->getId());
        $this->assertSame(['title' => 'Elastica guide'], $results->getResults()[0]->getData());
    }
}
```

The endpoint returns `Elastic\Elasticsearch\Response\Elasticsearch`, rather than
`Elastica\Response` or a `ResultSet`. The product header is required when
`setResponse()` validates a successful Elasticsearch response. Mock only
`search()` here so `getIndex()` continues to build a real `Index`. The constructor
is disabled for this example because the mocked endpoint never uses a transport;
other client methods that need configuration or a transport must be mocked too
if your application calls them.

For an empty result, use `hits.total.value = 0`, `hits.max_score = null`, and
`hits.hits = []`. The runnable [ClientMockingTest](../tests/ClientMockingTest.php)
covers both populated and empty responses, including the outgoing index and
query. With the development PHP container running, run the example tests
(no Elasticsearch server is needed):

```sh
make docker-run-phpunit PHPUNIT_OPTIONS="--group=unit tests/ClientMockingTest.php"
```

This approach checks application behavior for chosen responses. Keep functional
tests against Elasticsearch for server-side query semantics, mappings and
analysis.
