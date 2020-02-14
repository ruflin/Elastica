<?php

namespace Elastica\Test\Exception;

use Elastica\Document;
use Elastica\Exception\PartialShardFailureException;
use Elastica\JSON;
use Elastica\Query;
use Elastica\ResultSet\DefaultBuilder;

/**
 * @internal
 */
class PartialShardFailureExceptionTest extends AbstractExceptionTest
{
    /**
     * @group functional
     */
    public function testPartialFailure(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_partial_failure');
        $index->create(
            [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 5,
                        'number_of_replicas' => 0,
                    ],
                ],
            ],
            true
        );

        $index->addDocument(new Document('', ['name' => 'ruflin']));
        $index->addDocument(new Document('', ['name' => 'bobrik']));
        $index->addDocument(new Document('', ['name' => 'kimchy']));

        $index->refresh();

        $query = Query::create([
            'query' => [
                'bool' => [
                    'filter' => [
                        'script' => [
                            'script' => 'doc["undefined"] > 8', // compiles, but doesn't work
                        ],
                    ],
                ],
            ],
        ]);

        try {
            $index->search($query);

            $this->fail('PartialShardFailureException should have been thrown');
        } catch (PartialShardFailureException $e) {
            $builder = new DefaultBuilder();
            $resultSet = $builder->buildResultSet($e->getResponse(), $query);
            $this->assertCount(0, $resultSet->getResults());

            $message = JSON::parse($e->getMessage());
            $this->assertArrayHasKey('failures', $message, 'Failures are absent');
            $this->assertGreaterThan(0, \count($message['failures']), 'Failures are empty');
        }
    }
}
