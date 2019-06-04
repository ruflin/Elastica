<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Request;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class ResponseTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testResponse()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $mapping = new Mapping($type, [
            'name' => ['type' => 'text'],
            'dtmPosted' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        ]);
        $type->setMapping($mapping);

        $type->addDocuments([
            new Document(1, ['name' => 'nicolas ruflin', 'dtmPosted' => '2011-06-23 21:53:00']),
            new Document(2, ['name' => 'raul martinez jr', 'dtmPosted' => '2011-06-23 09:53:00']),
            new Document(3, ['name' => 'rachelle clemente', 'dtmPosted' => '2011-07-08 08:53:00']),
            new Document(4, ['name' => 'elastica search', 'dtmPosted' => '2011-07-08 01:53:00']),
        ]);

        $query = new Query();
        $query->setQuery(new MatchAll());
        $index->refresh();

        $resultSet = $type->search($query);

        $engineTime = $resultSet->getResponse()->getEngineTime();
        $shardsStats = $resultSet->getResponse()->getShardsStatistics();

        $this->assertInternalType('int', $engineTime);
        $this->assertInternalType('array', $shardsStats);
        $this->assertArrayHasKey('total', $shardsStats);
        $this->assertArrayHasKey('successful', $shardsStats);
    }

    /**
     * @group functional
     */
    public function testIsOk()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $doc = new Document(1, ['name' => 'ruflin']);
        $response = $type->addDocument($doc);

        $this->assertTrue($response->isOk());
    }

    /**
     * @group functional
     */
    public function testIsOkMultiple()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $docs = [
            new Document(1, ['name' => 'ruflin']),
            new Document(2, ['name' => 'ruflin']),
        ];
        $response = $type->addDocuments($docs);

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsOkBulkWithErrorsField()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'errors' => false,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsNotOkBulkWithErrorsField()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'errors' => true,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsOkBulkItemsWithOkField()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'ok' => true]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'ok' => true]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testStringErrorMessage()
    {
        $response = new Response(\json_encode([
            'error' => 'a',
        ]));

        $this->assertEquals('a', $response->getErrorMessage());
    }

    /**
     * @group unit
     */
    public function testArrayErrorMessage()
    {
        $response = new Response(\json_encode([
            'error' => ['a', 'b'],
        ]));

        $this->assertEquals(['a', 'b'], $response->getFullError());
    }

    /**
     * @group unit
     */
    public function testIsNotOkBulkItemsWithOkField()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'ok' => true]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'ok' => false]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsOkBulkItemsWithStatusField()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));

        $this->assertTrue($response->isOk());
    }

    /**
     * @group unit
     */
    public function testIsNotOkBulkItemsWithStatusField()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 301]],
            ],
        ]));

        $this->assertFalse($response->isOk());
    }

    /**
     * @group unit
     */
    public function testDecodeResponseWithBigIntSetToTrue()
    {
        $response = new Response(\json_encode([
            'took' => 213,
            'items' => [
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707891', '_version' => 4, 'status' => 200]],
                ['index' => ['_index' => 'rohlik', '_type' => 'grocery', '_id' => '707893', '_version' => 4, 'status' => 200]],
            ],
        ]));
        $response->setJsonBigintConversion(true);

        $this->assertInternalType('array', $response->getData());
    }

    /**
     * @group functional
     */
    public function testGetDataEmpty()
    {
        $index = $this->_createIndex();

        try {
            $response = $index->request(
                'non-existent-type/_mapping',
                Request::GET,
                [],
                ['include_type_name' => true]
            )->getData();
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertEquals('type_missing_exception', $error['type']);
            $this->assertContains('non-existent-type', $error['reason']);
        }

        $this->assertNull($response);
    }
}
