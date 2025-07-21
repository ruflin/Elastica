<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Bulk\Action;
use Elastica\Bulk\Action\AbstractDocument;
use Elastica\Bulk\Action\CreateDocument;
use Elastica\Bulk\Action\IndexDocument;
use Elastica\Bulk\Action\UpdateDocument;
use Elastica\Bulk\Response;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\RequestEntityTooLargeException;
use Elastica\Response as ElasticaResponse;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class BulkTest extends BaseTest
{
    #[Group('functional')]
    public function testSend(): void
    {
        $index = $this->_createIndex();
        $indexName = $index->getName();
        $client = $index->getClient();

        $newDocument1 = new Document('1', ['name' => 'Mister Fantastic'], $index);
        $newDocument2 = new Document('2', ['name' => 'Invisible Woman']);
        $newDocument3 = new Document('3', ['name' => 'The Human Torch'], $index);
        $newDocument4 = new Document(null, ['name' => 'The Thing'], $index);

        $newDocument3->setOpType(Document::OP_TYPE_CREATE);

        $documents = [
            $newDocument1,
            $newDocument2,
            $newDocument3,
            $newDocument4,
        ];

        $bulk = new Bulk($client);
        $bulk->setIndex($index);
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
            ['index' => ['_id' => '1', '_index' => $indexName]],
            ['name' => 'Mister Fantastic'],
            ['index' => ['_id' => '2']],
            ['name' => 'Invisible Woman'],
            ['create' => ['_id' => '3', '_index' => $indexName]],
            ['name' => 'The Human Torch'],
            ['index' => ['_index' => $indexName]],
            ['name' => 'The Thing'],
        ];
        $this->assertEquals($expected, $data);

        $expected = '{"index":{"_id":"1","_index":"'.$indexName.'"}}
{"name":"Mister Fantastic"}
{"index":{"_id":"2"}}
{"name":"Invisible Woman"}
{"create":{"_id":"3","_index":"'.$indexName.'"}}
{"name":"The Human Torch"}
{"index":{"_index":"'.$indexName.'"}}
{"name":"The Thing"}
';

        $expected = \str_replace(\PHP_EOL, "\n", $expected);
        $this->assertEquals($expected, (string) \str_replace(\PHP_EOL, "\n", (string) $bulk));

        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        foreach ($response as $i => $bulkResponse) {
            $this->assertInstanceOf(Response::class, $bulkResponse);
            $this->assertTrue($bulkResponse->isOk());
            $this->assertFalse($bulkResponse->hasError());
            $this->assertSame($actions[$i], $bulkResponse->getAction());
        }

        $index->refresh();

        $this->assertEquals(4, $index->count());

        $bulk = new Bulk($client);
        $bulk->addDocument($newDocument3, Action::OP_TYPE_DELETE);

        $data = $bulk->toArray();

        $expected = [
            ['delete' => ['_index' => $indexName, '_id' => '3']],
        ];
        $this->assertEquals($expected, $data);

        $bulk->send();

        $index->refresh();

        $this->assertEquals(3, $index->count());

        try {
            $index->getDocument(3);
            $this->fail('Document #3 should be deleted');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    #[Group('functional')]
    public function testUnicodeBulkSend(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument1 = new Document('1', ['name' => 'Сегодня, я вижу, особенно грустен твой взгляд,'], $index);
        $newDocument2 = new Document('2', ['name' => 'И руки особенно тонки, колени обняв.']);
        $newDocument3 = new Document('3', ['name' => 'Послушай: далеко, далеко, на озере Чад / Изысканный бродит жираф.'], $index);

        $documents = [
            $newDocument1,
            $newDocument2,
            $newDocument3,
        ];

        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addDocuments($documents);

        $actions = $bulk->getActions();

        $this->assertSame($newDocument1, $actions[0]->getDocument());
        $this->assertSame($newDocument2, $actions[1]->getDocument());
        $this->assertSame($newDocument3, $actions[2]->getDocument());
    }

    #[Group('functional')]
    public function testSetIndex(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('index');

        $index2 = $client->getIndex('index2');

        $bulk = new Bulk($client);

        $this->assertFalse($bulk->hasIndex());

        $bulk->setIndex($index);
        $this->assertTrue($bulk->hasIndex());
        $this->assertEquals('index', $bulk->getIndex());

        $bulk->setIndex($index2);
        $this->assertTrue($bulk->hasIndex());
        $this->assertEquals('index2', $bulk->getIndex());

        $bulk->setIndex($index);
        $this->assertTrue($bulk->hasIndex());
        $this->assertEquals('index', $bulk->getIndex());
    }

    #[Group('unit')]
    public function testAddActions(): void
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $action1 = new Action(Action::OP_TYPE_DELETE);
        $action1->setIndex('index');
        $action1->setId('1');

        $action2 = new Action(Action::OP_TYPE_INDEX);
        $action2->setIndex('index');
        $action2->setId('1');
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

    #[Group('unit')]
    public function testAddRawData(): void
    {
        $bulk = new Bulk($this->_getClient());

        $rawData = [
            ['index' => ['_index' => 'test', '_id' => '1']],
            ['user' => ['name' => 'hans']],
            ['delete' => ['_index' => 'test', '_id' => '2']],
            ['delete' => ['_index' => 'test', '_id' => '3']],
            ['create' => ['_index' => 'test', '_id' => '4']],
            ['user' => ['name' => 'mans']],
            ['delete' => ['_index' => 'test', '_id' => '5']],
        ];

        $bulk->addRawData($rawData);

        $actions = $bulk->getActions();

        $this->assertIsArray($actions);
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

    #[DataProvider('invalidRawDataProvider')]
    #[Group('unit')]
    public function testInvalidRawData($rawData, $failMessage): void
    {
        $this->expectException(InvalidException::class);

        $bulk = new Bulk($this->_getClient());

        $bulk->addRawData($rawData);

        $this->fail($failMessage);
    }

    public static function invalidRawDataProvider(): array
    {
        return [
            [
                [
                    ['index' => ['_index' => 'test', '_id' => '1']],
                    ['user' => ['name' => 'hans']],
                    ['user' => ['name' => 'mans']],
                ],
                'Two sources for one action',
            ],
            [
                [
                    ['index' => ['_index' => 'test', '_id' => '1']],
                    ['user' => ['name' => 'hans']],
                    ['upsert' => ['_index' => 'test', '_id' => '2']],
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

    #[Group('functional')]
    public function testErrorRequest(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $documents = [
            new Document('1', ['name' => 'Mister Fantastic'], $index),
            new Document('2', ['name' => 'Invisible Woman'], $index),
            new Document('2', ['name' => 'The Human Torch'], $index),
        ];

        $documents[2]->setOpType(Document::OP_TYPE_CREATE);

        $bulk = new Bulk($client);
        $bulk->addDocuments($documents);

        try {
            $bulk->send();
            $this->fail('3rd document create should produce error');
        } catch (ResponseException $e) {
            $error = $e->getResponseSet()->getFullError();
            $this->assertSame('version_conflict_engine_exception', $error['type']);
            $failures = $e->getFailures();
            $this->assertIsArray($failures);
            $this->assertArrayHasKey(0, $failures);
        }
    }

    #[Group('functional')]
    public function testRawDocumentDataRequest(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $documents = [
            new Document(null, '{"name":"Mister Fantastic"}'),
            new Document(null, '{"name":"Invisible Woman"}'),
            new Document(null, '{"name":"The Human Torch"}'),
        ];

        $bulk = (new Bulk($client))
            ->setIndex($index)
            ->addDocuments($documents)
        ;

        $expectedJson = <<<'JSON'
            {"index":{}}
            {"name":"Mister Fantastic"}
            {"index":{}}
            {"name":"Invisible Woman"}
            {"index":{}}
            {"name":"The Human Torch"}
            JSON;

        $expectedJson = \str_replace(\PHP_EOL, "\n", $expectedJson);
        $this->assertSame($expectedJson, \trim((string) $bulk));

        $response = $bulk->send();
        $this->assertTrue($response->isOk());

        $index->refresh();

        $response = $index->search();
        $this->assertEquals(3, $response->count());

        foreach (['Mister', 'Invisible', 'Torch'] as $name) {
            $result = $index->search($name);
            $this->assertCount(1, $result->getResults());
        }
    }

    #[Group('functional')]
    public function testUpdate(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $doc1 = new Document('1', ['name' => 'John'], $index);
        $doc2 = new Document('2', ['name' => 'Paul'], $index);
        $doc3 = new Document('3', ['name' => 'George'], $index);
        $doc4 = new Document('4', ['name' => 'Ringo'], $index);
        $documents = [$doc1, $doc2, $doc3, $doc4];

        // index some documents
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addDocuments($documents);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $index->getDocument(2);

        // test updating via document
        $doc2 = new Document('2', ['name' => 'The Walrus'], $index);
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $updateAction = new UpdateDocument($doc2);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $index->getDocument(2);
        $docData = $doc->getData();
        $this->assertEquals('The Walrus', $docData['name']);

        // test updating via script
        $script = new Script('ctx._source.name += params.param1;', ['param1' => ' was Paul'], Script::LANG_PAINLESS, '2');
        $updateAction = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc2 = $index->getDocument(2);
        $this->assertEquals('The Walrus was Paul', $doc2->name);

        // test upsert
        $script = new Script('', [], null, '5');
        $doc = new Document('', ['counter' => 1]);
        $script->setUpsert($doc);
        $updateAction = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $index->getDocument(5);
        $this->assertEquals(1, $doc->counter);

        // test doc_as_upsert
        $doc = new Document('6', ['test' => 'test']);
        $doc->setDocAsUpsert(true);
        $updateAction = AbstractDocument::create($doc, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $index->getDocument(6);
        $this->assertEquals('test', $doc->test);

        // test doc_as_upsert with set of documents (use of addDocuments)
        $doc1 = new Document('7', ['test' => 'test1']);
        $doc1->setDocAsUpsert(true);
        $doc2 = new Document('8', ['test' => 'test2']);
        $doc2->setDocAsUpsert(true);
        $docs = [$doc1, $doc2];
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addDocuments($docs, Action::OP_TYPE_UPDATE);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $index->getDocument(7);
        $this->assertEquals('test1', $doc->test);
        $doc = $index->getDocument(8);
        $this->assertEquals('test2', $doc->test);

        // test updating via document with json string as data
        $doc3 = new Document('2', [], $index);
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $doc3->setData('{"name" : "Paul it is"}');
        $updateAction = new UpdateDocument($doc3);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $index->getDocument(2);
        $docData = $doc->getData();
        $this->assertEquals('Paul it is', $docData['name']);

        $index->delete();
    }

    #[Group('functional')]
    public function testUpsert(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $doc1 = new Document('1', ['name' => 'Pele'], $index);
        $doc2 = new Document('2', ['name' => 'Beckenbauer'], $index);
        $doc3 = new Document('3', ['name' => 'Baggio'], $index);
        $doc4 = new Document('4', ['name' => 'Cruyff'], $index);
        $documents = \array_map(static function ($d) {
            $d->setDocAsUpsert(true);

            return $d;
        }, [$doc1, $doc2, $doc3, $doc4]);

        // index some documents
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $bulk->addDocuments($documents);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        // test updating via document
        $doc1 = new Document('1', ['name' => 'Maradona'], $index);
        $doc1->setDocAsUpsert(true);
        $bulk = new Bulk($client);
        $bulk->setIndex($index);
        $updateAction = new UpdateDocument($doc1);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $index->getDocument(1);
        $docData = $doc->getData();
        $this->assertEquals('Maradona', $docData['name']);
    }

    #[Group('functional')]
    public function testScriptUpsert(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $bulk = new Bulk($client);
        $bulk->setIndex($index);

        $id = '1';
        $field = 123456789;
        $subField = ['field' => 'a'];
        $subField2 = ['field_2' => 'b'];

        $defaultData = [
            'field' => null,
            'sub_field' => [],
            'sub_field_2' => [],
        ];

        // insert doc and update field
        $script = new Script('ctx._source.field = params.field', ['field' => $field]);
        $script->setUpsert($defaultData);
        $script->setId($id);
        $script->setScriptedUpsert(true);

        $action = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk->addAction($action);

        // update sub_field
        $script = new Script('if ( !ctx._source.sub_field.contains(params) ) ctx._source.sub_field.add(params)', $subField);
        $script->setUpsert($defaultData);
        $script->setId($id);
        $script->setScriptedUpsert(true);

        $action = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk->addAction($action);

        // update sub_field_2
        $script = new Script('if ( !ctx._source.sub_field_2.contains(params) ) ctx._source.sub_field_2.add(params)', $subField2);
        $script->setUpsert($defaultData);
        $script->setId($id);
        $script->setScriptedUpsert(true);

        $action = AbstractDocument::create($script, Action::OP_TYPE_UPDATE);
        $bulk->addAction($action);

        $response = $bulk->send();

        $this->assertTrue($response->isOk());

        $index->refresh();

        $doc = $index->getDocument($id);

        $this->assertEquals($field, $doc->getData()['field']);

        $this->assertCount(1, $doc->getData()['sub_field']);
        $this->assertCount(1, $doc->getData()['sub_field_2']);

        $this->assertEquals($subField['field'], $doc->getData()['sub_field'][0]['field']);
        $this->assertEquals($subField2['field_2'], $doc->getData()['sub_field_2'][0]['field_2']);
    }

    #[Group('unit')]
    public function testGetPath(): void
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $this->assertEquals('_bulk', $bulk->getPath());

        $indexName = 'testIndex';

        $bulk->setIndex($indexName);
        $this->assertEquals($indexName.'/_bulk', $bulk->getPath());
    }

    #[Group('functional')]
    public function testRetry(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $doc1 = new Document('1', ['name' => 'Mister Fantastic'], $index);
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

        // Test retry via client
        $client->setConfigValue('retryOnConflict', 5);
        $doc2 = new Document('2', ['name' => 'Invisible Woman']);
        $doc2->setOpType(Action::OP_TYPE_UPDATE);

        $bulk = new Bulk($client);
        $bulk->addDocument($doc2);

        $actions = $bulk->getActions();

        $metadata = $actions[0]->getMetadata();
        $this->assertEquals(5, $metadata['retry_on_conflict']);

        $script = new Script('');

        $bulk = new Bulk($client);
        $bulk->addScript($script);

        $actions = $bulk->getActions();

        $metadata = $actions[0]->getMetadata();
        $this->assertEquals(5, $metadata['retry_on_conflict']);
    }

    #[Group('unit')]
    public function testSetShardTimeout(): void
    {
        $bulk = new Bulk($this->_getClient());

        $this->assertSame($bulk, $bulk->setShardTimeout('10s'));
    }

    #[Group('unit')]
    public function testSetRequestParam(): void
    {
        $bulk = new Bulk($this->_getClient());

        $this->assertSame($bulk, $bulk->setRequestParam('key', 'value'));
    }

    #[Group('benchmark')]
    public function testMemoryUsage(): void
    {
        $index = $this->_createIndex();

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

            $index->addDocuments($docs);
            $docs = [];
        }

        unset($docs);

        $endMemory = \memory_get_usage();

        $this->assertLessThan(1.41, $endMemory / $startMemory);
    }

    #[Group('unit')]
    public function testHasIndex(): void
    {
        $client = $this->_getClient();
        $bulk = new Bulk($client);

        $this->assertFalse($bulk->hasIndex());
        $bulk->setIndex('unittest');
        $this->assertTrue($bulk->hasIndex());
    }

    #[Group('unit')]
    public function testSendRequestEntityTooLargeExceptionIf413Response(): void
    {
        $response = new ElasticaResponse('', 413);

        /** @var Client|MockObject $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->method('baseBulk')
            ->willReturn($response)
        ;

        $documents = [
            new Document('1', ['name' => 'Mister Fantastic']),
            new Document('2', ['name' => 'Invisible Woman']),
            new Document('2', ['name' => 'The Human Torch']),
        ];

        $bulk = new Bulk($clientMock);
        $bulk->addDocuments($documents);

        $this->expectException(RequestEntityTooLargeException::class);
        $bulk->send();
    }
}
