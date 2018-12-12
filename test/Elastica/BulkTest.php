<?php

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Bulk\Action;
use Elastica\Bulk\Action\AbstractDocument;
use Elastica\Bulk\Action\CreateDocument;
use Elastica\Bulk\Action\IndexDocument;
use Elastica\Bulk\Action\UpdateDocument;
use Elastica\Bulk\Response;
use Elastica\Bulk\ResponseSet;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Exception\NotFoundException;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;

class BulkTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSend()
    {
        $index = $this->_createIndex();
        $indexName = $index->getName();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument1 = $type->createDocument(1, ['name' => 'Mister Fantastic']);
        $newDocument2 = new Document(2, ['name' => 'Invisible Woman']);
        $newDocument3 = $type->createDocument(3, ['name' => 'The Human Torch']);
        $newDocument4 = $type->createDocument(null, ['name' => 'The Thing']);

        $newDocument3->setOpType(Document::OP_TYPE_CREATE);

        $documents = [
            $newDocument1,
            $newDocument2,
            $newDocument3,
            $newDocument4,
        ];

        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments($documents);

        $actions = $bulk->getActions();

        $this->assertInstanceOf(IndexDocument::class, $actions[0]);
        $this->assertEquals('index', $actions[0]->getOpType());
        $this->assertSame($newDocument1, $actions[0]->getDocument());

        $this->assertInstanceOf(IndexDocument::class, $actions[1]);
        $this->assertEquals('index', $actions[1]->getOpType());
        $this->assertSame($newDocument2, $actions[1]->getDocument());

        $this->assertInstanceOf(CreateDocument::class, $actions[2]);
        $this->assertEquals('create', $actions[2]->getOpType());
        $this->assertSame($newDocument3, $actions[2]->getDocument());

        $this->assertInstanceOf(IndexDocument::class, $actions[3]);
        $this->assertEquals('index', $actions[3]->getOpType());
        $this->assertSame($newDocument4, $actions[3]->getDocument());

        $data = $bulk->toArray();

        $expected = [
            ['index' => ['_id' => 1, '_type' => '_doc', '_index' => $indexName]],
            ['name' => 'Mister Fantastic'],
            ['index' => ['_id' => 2]],
            ['name' => 'Invisible Woman'],
            ['create' => ['_id' => 3, '_type' => '_doc', '_index' => $indexName]],
            ['name' => 'The Human Torch'],
            ['index' => ['_type' => '_doc', '_index' => $indexName]],
            ['name' => 'The Thing'],
        ];
        $this->assertEquals($expected, $data);

        $expected = '{"index":{"_id":1,"_type":"_doc","_index":"'.$indexName.'"}}
{"name":"Mister Fantastic"}
{"index":{"_id":2}}
{"name":"Invisible Woman"}
{"create":{"_id":3,"_type":"_doc","_index":"'.$indexName.'"}}
{"name":"The Human Torch"}
{"index":{"_type":"_doc","_index":"'.$indexName.'"}}
{"name":"The Thing"}
';

        $expected = \str_replace(PHP_EOL, "\n", $expected);
        $this->assertEquals($expected, (string) \str_replace(PHP_EOL, "\n", (string) $bulk));

        $response = $bulk->send();

        $this->assertInstanceOf(ResponseSet::class, $response);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        foreach ($response as $i => $bulkResponse) {
            $this->assertInstanceOf(Response::class, $bulkResponse);
            $this->assertTrue($bulkResponse->isOk());
            $this->assertFalse($bulkResponse->hasError());
            $this->assertSame($actions[$i], $bulkResponse->getAction());
        }

        $type->getIndex()->refresh();

        $this->assertEquals(4, $type->count());

        $bulk = new Bulk($client);
        $bulk->addDocument($newDocument3, Action::OP_TYPE_DELETE);

        $data = $bulk->toArray();

        $expected = [
            ['delete' => ['_index' => $indexName, '_type' => '_doc', '_id' => 3]],
        ];
        $this->assertEquals($expected, $data);

        $bulk->send();

        $type->getIndex()->refresh();

        $this->assertEquals(3, $type->count());

        try {
            $type->getDocument(3);
            $this->fail('Document #3 should be deleted');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @group functional
     */
    public function testUnicodeBulkSend()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument1 = $type->createDocument(1, ['name' => 'Сегодня, я вижу, особенно грустен твой взгляд,']);
        $newDocument2 = new Document(2, ['name' => 'И руки особенно тонки, колени обняв.']);
        $newDocument3 = $type->createDocument(3, ['name' => 'Послушай: далеко, далеко, на озере Чад / Изысканный бродит жираф.']);

        $documents = [
            $newDocument1,
            $newDocument2,
            $newDocument3,
        ];

        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments($documents);

        $actions = $bulk->getActions();

        $this->assertSame($newDocument1, $actions[0]->getDocument());
        $this->assertSame($newDocument2, $actions[1]->getDocument());
        $this->assertSame($newDocument3, $actions[2]->getDocument());
    }

    /**
     * @group functional
     */
    public function testSetIndexType()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('index');
        $type = $index->getType('_doc');

        $index2 = $client->getIndex('index2');
        $type2 = $index2->getType('_doc');

        $bulk = new Bulk($client);

        $this->assertFalse($bulk->hasIndex());
        $this->assertFalse($bulk->hasType());

        $bulk->setIndex($index);
        $this->assertTrue($bulk->hasIndex());
        $this->assertFalse($bulk->hasType());
        $this->assertEquals('index', $bulk->getIndex());

        $bulk->setType($type2);
        $this->assertTrue($bulk->hasIndex());
        $this->assertTrue($bulk->hasType());
        $this->assertEquals('index2', $bulk->getIndex());
        $this->assertEquals('_doc', $bulk->getType());

        $bulk->setType($type);
        $this->assertTrue($bulk->hasIndex());
        $this->assertTrue($bulk->hasType());
        $this->assertEquals('index', $bulk->getIndex());
        $this->assertEquals('_doc', $bulk->getType());

        $bulk->setIndex($index2);
        $this->assertTrue($bulk->hasIndex());
        $this->assertTrue($bulk->hasType());
        $this->assertEquals('index2', $bulk->getIndex());
        $this->assertEquals('_doc', $bulk->getType());
    }

    /**
     * @group unit
     */
    public function testAddActions()
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $action1 = new Action(Action::OP_TYPE_DELETE);
        $action1->setIndex('index');
        $action1->setType('type');
        $action1->setId(1);

        $action2 = new Action(Action::OP_TYPE_INDEX);
        $action2->setIndex('index');
        $action2->setType('type');
        $action2->setId(1);
        $action2->setSource(['name' => 'Batman']);

        $actions = [
            $action1,
            $action2,
        ];

        $bulk->addActions($actions);

        $getActions = $bulk->getActions();

        $this->assertSame($action1, $getActions[0]);
        $this->assertSame($action2, $getActions[1]);
    }

    /**
     * @group unit
     */
    public function testAddRawData()
    {
        $bulk = new Bulk($this->_getClient());

        $rawData = [
            ['index' => ['_index' => 'test', '_type' => 'user', '_id' => '1']],
            ['user' => ['name' => 'hans']],
            ['delete' => ['_index' => 'test', '_type' => 'user', '_id' => '2']],
            ['delete' => ['_index' => 'test', '_type' => 'user', '_id' => '3']],
            ['create' => ['_index' => 'test', '_type' => 'user', '_id' => '4']],
            ['user' => ['name' => 'mans']],
            ['delete' => ['_index' => 'test', '_type' => 'user', '_id' => '5']],
        ];

        $bulk->addRawData($rawData);

        $actions = $bulk->getActions();

        $this->assertInternalType('array', $actions);
        $this->assertCount(5, $actions);

        $this->assertInstanceOf(Action::class, $actions[0]);
        $this->assertEquals('index', $actions[0]->getOpType());
        $this->assertEquals($rawData[0]['index'], $actions[0]->getMetadata());
        $this->assertTrue($actions[0]->hasSource());
        $this->assertEquals($rawData[1], $actions[0]->getSource());

        $this->assertInstanceOf(Action::class, $actions[1]);
        $this->assertEquals('delete', $actions[1]->getOpType());
        $this->assertEquals($rawData[2]['delete'], $actions[1]->getMetadata());
        $this->assertFalse($actions[1]->hasSource());

        $this->assertInstanceOf(Action::class, $actions[2]);
        $this->assertEquals('delete', $actions[2]->getOpType());
        $this->assertEquals($rawData[3]['delete'], $actions[2]->getMetadata());
        $this->assertFalse($actions[2]->hasSource());

        $this->assertInstanceOf(Action::class, $actions[3]);
        $this->assertEquals('create', $actions[3]->getOpType());
        $this->assertEquals($rawData[4]['create'], $actions[3]->getMetadata());
        $this->assertTrue($actions[3]->hasSource());
        $this->assertEquals($rawData[5], $actions[3]->getSource());

        $this->assertInstanceOf(Action::class, $actions[4]);
        $this->assertEquals('delete', $actions[4]->getOpType());
        $this->assertEquals($rawData[6]['delete'], $actions[4]->getMetadata());
        $this->assertFalse($actions[4]->hasSource());
    }

    /**
     * @group unit
     * @dataProvider invalidRawDataProvider
     */
    public function testInvalidRawData($rawData, $failMessage)
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $bulk = new Bulk($this->_getClient());

        $bulk->addRawData($rawData);

        $this->fail($failMessage);
    }

    public function invalidRawDataProvider()
    {
        return [
            [
                [
                    ['index' => ['_index' => 'test', '_type' => 'user', '_id' => '1']],
                    ['user' => ['name' => 'hans']],
                    ['user' => ['name' => 'mans']],
                ],
                'Two sources for one action',
            ],
            [
                [
                    ['index' => ['_index' => 'test', '_type' => 'user', '_id' => '1']],
                    ['user' => ['name' => 'hans']],
                    ['upsert' => ['_index' => 'test', '_type' => 'user', '_id' => '2']],
                ],
                'Invalid optype for action',
            ],
            [
                [
                    ['user' => ['name' => 'mans']],
                ],
                'Source without action',
            ],
            [
                [
                    [],
                ],
                'Empty array',
            ],
            [
                [
                    'dummy',
                ],
                'String as data',
            ],
        ];
    }

    /**
     * @group functional
     */
    public function testErrorRequest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $documents = [
            $type->createDocument(1, ['name' => 'Mister Fantastic']),
            $type->createDocument(2, ['name' => 'Invisible Woman']),
            $type->createDocument(2, ['name' => 'The Human Torch']),
        ];

        $documents[2]->setOpType(Document::OP_TYPE_CREATE);

        $bulk = new Bulk($client);
        $bulk->addDocuments($documents);

        try {
            $bulk->send();
            $bulk->fail('3rd document create should produce error');
        } catch (ResponseException $e) {
            $error = $e->getResponseSet()->getFullError();
            $this->assertContains('version_conflict_engine_exception', $error['type']);
            $failures = $e->getFailures();
            $this->assertInternalType('array', $failures);
            $this->assertArrayHasKey(0, $failures);
        }
    }

    /**
     * @group functional
     */
    public function testRawDocumentDataRequest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $documents = [
            new Document(null, '{"name":"Mister Fantastic"}'),
            new Document(null, '{"name":"Invisible Woman"}'),
            new Document(null, '{"name":"The Human Torch"}'),
        ];

        $bulk = new Bulk($client);
        $bulk->addDocuments($documents);
        $bulk->setType($type);

        $expectedJson = '{"index":{}}
{"name":"Mister Fantastic"}
{"index":{}}
{"name":"Invisible Woman"}
{"index":{}}
{"name":"The Human Torch"}
';
        $expectedJson = \str_replace(PHP_EOL, "\n", $expectedJson);
        $this->assertEquals($expectedJson, $bulk->toString());

        $response = $bulk->send();
        $this->assertTrue($response->isOk());

        $type->getIndex()->refresh();

        $response = $type->search();
        $this->assertEquals(3, $response->count());

        foreach (['Mister', 'Invisible', 'Torch'] as $name) {
            $result = $type->search($name);
            $this->assertCount(1, $result->getResults());
        }
    }

    /**
     * @group functional
     */
    public function testUpdate()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $doc1 = $type->createDocument(1, ['name' => 'John']);
        $doc2 = $type->createDocument(2, ['name' => 'Paul']);
        $doc3 = $type->createDocument(3, ['name' => 'George']);
        $doc4 = $type->createDocument(4, ['name' => 'Ringo']);
        $documents = [$doc1, $doc2, $doc3, $doc4];

        //index some documents
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments($documents);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        //test updating via document
        $doc2 = $type->createDocument(2, ['name' => 'The Walrus']);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $updateAction = new UpdateDocument($doc2);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $type->getDocument(2);
        $docData = $doc->getData();
        $this->assertEquals('The Walrus', $docData['name']);

        //test updating via script
        $script = new Script('ctx._source.name += params.param1;', ['param1' => ' was Paul'], Script::LANG_PAINLESS, 2);
        $updateAction = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc2 = $type->getDocument(2);
        $this->assertEquals('The Walrus was Paul', $doc2->name);

        //test upsert
        $script = new Script('', [], null, 5);
        $doc = new Document('', ['counter' => 1]);
        $script->setUpsert($doc);
        $updateAction = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $type->getDocument(5);
        $this->assertEquals(1, $doc->counter);

        //test doc_as_upsert
        $doc = new Document(6, ['test' => 'test']);
        $doc->setDocAsUpsert(true);
        $updateAction = AbstractDocument::create($doc, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $type->getDocument(6);
        $this->assertEquals('test', $doc->test);

        //test doc_as_upsert with set of documents (use of addDocuments)
        $doc1 = new Document(7, ['test' => 'test1']);
        $doc1->setDocAsUpsert(true);
        $doc2 = new Document(8, ['test' => 'test2']);
        $doc2->setDocAsUpsert(true);
        $docs = [$doc1, $doc2];
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments($docs, Action::OP_TYPE_UPDATE);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $type->getDocument(7);
        $this->assertEquals('test1', $doc->test);
        $doc = $type->getDocument(8);
        $this->assertEquals('test2', $doc->test);

        //test updating via document with json string as data
        $doc3 = $type->createDocument(2);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $doc3->setData('{"name" : "Paul it is"}');
        $updateAction = new UpdateDocument($doc3);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $type->getDocument(2);
        $docData = $doc->getData();
        $this->assertEquals('Paul it is', $docData['name']);

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testUpsert()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $doc1 = $type->createDocument(1, ['name' => 'Pele']);
        $doc2 = $type->createDocument(2, ['name' => 'Beckenbauer']);
        $doc3 = $type->createDocument(3, ['name' => 'Baggio']);
        $doc4 = $type->createDocument(4, ['name' => 'Cruyff']);
        $documents = \array_map(function ($d) {
            $d->setDocAsUpsert(true);

            return $d;
        }, [$doc1, $doc2, $doc3, $doc4]);

        //index some documents
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments($documents);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        //test updating via document
        $doc1 = $type->createDocument(1, ['name' => 'Maradona']);
        $doc1->setDocAsUpsert(true);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $updateAction = new UpdateDocument($doc1);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $type->getDocument(1);
        $docData = $doc->getData();
        $this->assertEquals('Maradona', $docData['name']);
    }

    /**
     * @group unit
     */
    public function testGetPath()
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $this->assertEquals('_bulk', $bulk->getPath());

        $indexName = 'testIndex';

        $bulk->setIndex($indexName);
        $this->assertEquals($indexName.'/_bulk', $bulk->getPath());

        $typeName = 'testType';
        $bulk->setType($typeName);
        $this->assertEquals($indexName.'/'.$typeName.'/_bulk', $bulk->getPath());
    }

    /**
     * @group functional
     */
    public function testRetry()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $doc1 = $type->createDocument(1, ['name' => 'Mister Fantastic']);
        $doc1->setOpType(Action::OP_TYPE_UPDATE);
        $doc1->setRetryOnConflict(5);

        $bulk = new Bulk($client);
        $bulk->addDocument($doc1);

        $actions = $bulk->getActions();

        $metadata = $actions[0]->getMetadata();
        $this->assertEquals(5, $metadata['retry_on_conflict']);

        $script = new Script('');
        $script->setRetryOnConflict(5);

        $bulk = new Bulk($client);
        $bulk->addScript($script);

        $actions = $bulk->getActions();

        $metadata = $actions[0]->getMetadata();
        $this->assertEquals(5, $metadata['retry_on_conflict']);
    }

    /**
     * @group unit
     */
    public function testSetShardTimeout()
    {
        $bulk = new Bulk($this->_getClient());
        $this->assertInstanceOf(Bulk::class, $bulk->setShardTimeout(10));
    }

    /**
     * @group unit
     */
    public function testSetRequestParam()
    {
        $bulk = new Bulk($this->_getClient());
        $this->assertInstanceOf(Bulk::class, $bulk->setRequestParam('key', 'value'));
    }

    /**
     * @group benchmark
     */
    public function testMemoryUsage()
    {
        $type = $this->_createIndex()->getType('_doc');

        $data = [
            'text1' => 'Very long text for a string',
            'text2' => 'But this is not very long',
            'text3' => 'random or not random?',
        ];

        $startMemory = \memory_get_usage();

        for ($n = 1; $n < 10; ++$n) {
            $docs = [];

            for ($i = 1; $i <= 3000; ++$i) {
                $docs[] = new Document(\uniqid(), $data);
            }

            $type->addDocuments($docs);
            $docs = [];
        }

        unset($docs);

        $endMemory = \memory_get_usage();

        $this->assertLessThan($endMemory / $startMemory, 1.3);
    }

    /**
     * @group unit
     */
    public function testHasIndex()
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $this->assertFalse($bulk->hasIndex());
        $bulk->setIndex('unittest');
        $this->assertTrue($bulk->hasIndex());
    }

    /**
     * @group unit
     */
    public function testHasType()
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $this->assertFalse($bulk->hasType());
        $bulk->setType('unittest');
        $this->assertTrue($bulk->hasType());
    }
}
