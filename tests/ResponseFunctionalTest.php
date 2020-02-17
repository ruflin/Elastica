<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;

/**
 * @group functional
 *
 * @internal
 */
class ResponseFunctionalTest extends BaseTest
{
    public function testResponse(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new  Mapping([
            'name' => ['type' => 'text'],
            'dtmPosted' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        ]));

        $index->addDocuments([
            new Document('1', ['name' => 'nicolas ruflin', 'dtmPosted' => '2011-06-23 21:53:00']),
            new Document('2', ['name' => 'raul martinez jr', 'dtmPosted' => '2011-06-23 09:53:00']),
            new Document('3', ['name' => 'rachelle clemente', 'dtmPosted' => '2011-07-08 08:53:00']),
            new Document('4', ['name' => 'elastica search', 'dtmPosted' => '2011-07-08 01:53:00']),
        ]);

        $query = new Query();
        $query->setQuery(new MatchAll());
        $index->refresh();

        $resultSet = $index->search($query);

        $engineTime = $resultSet->getResponse()->getEngineTime();
        $shardsStats = $resultSet->getResponse()->getShardsStatistics();

        $this->assertIsInt($engineTime);
        $this->assertIsArray($shardsStats);
        $this->assertArrayHasKey('total', $shardsStats);
        $this->assertArrayHasKey('successful', $shardsStats);
    }

    public function testIsOk(): void
    {
        $index = $this->_createIndex();

        $doc = new Document('1', ['name' => 'ruflin']);
        $response = $index->addDocument($doc);

        $this->assertTrue($response->isOk());
    }

    public function testIsOkMultiple(): void
    {
        $index = $this->_createIndex();
        $docs = [
            new Document('1', ['name' => 'ruflin']),
            new Document('2', ['name' => 'ruflin']),
        ];
        $response = $index->addDocuments($docs);

        $this->assertTrue($response->isOk());
    }

    public function testGetDataEmpty(): void
    {
        $index = $this->_createIndex();
        $gotException = false;

        try {
            $index->request(
                'non-existent-type/_mapping',
                Request::GET,
                [],
                ['include_type_name' => true]
            );
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertEquals('type_missing_exception', $error['type']);
            $this->assertStringContainsString('non-existent-type', $error['reason']);

            $gotException = true;
        }

        $this->assertTrue($gotException);
    }
}
