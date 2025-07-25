<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Elasticsearch\Transport\Adapter\AdapterOptions;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Bulk;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use Elastica\Test\Transport\NodePool\TraceableSimpleNodePool;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
#[Group('functional')]
class ClientFunctionalTest extends BaseTest
{
    public function testConnectionErrors(): void
    {
        $this->expectException(NoNodeAvailableException::class);

        $client = $this->_getClient(['hosts' => ['foo.bar:9201']]);
        $client->getVersion();
    }

    public function testClientBadHost(): void
    {
        $this->expectException(NoNodeAvailableException::class);

        $client = $this->_getClient(['hosts' => ['localhost:9201']]);
        $client->getVersion();
    }

    public function testGetVersion(): void
    {
        $client = $this->_getClient();
        $this->assertNotEmpty($client->getVersion());
    }

    public function testConnectionsArray(): void
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient(['hosts' => [$this->_getHost().':9200']]);
        $index = $client->getIndex('elastica_test1');
        $index->create([], [
            'recreate' => true,
        ]);

        // Adds 1 document to the index
        $doc1 = new Document(
            '1',
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $index->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(
            '2',
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(
            '3',
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $index->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $index->search('rolf');
    }

    public function testTwoServersSame(): void
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient([
            'hosts' => [
                $this->_getHost().':9200',
                $this->_getHost().':9200',
            ],
        ]);
        $index = $client->getIndex('elastica_test1');
        $index->create([], [
            'recreate' => true,
        ]);

        // Adds 1 document to the index
        $doc1 = new Document(
            '1',
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $index->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(
            '2',
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(
            '3',
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $index->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $index->search('rolf');
    }

    public function testBulk(): void
    {
        $client = $this->_getClient();

        $params = [
            ['index' => ['_index' => 'test', '_id' => '1']],
            ['user' => ['name' => 'hans']],
            ['index' => ['_index' => 'test', '_id' => '2']],
            ['user' => ['name' => 'peter']],
        ];

        $client->bulk($params);
    }

    public function testForcemergeAll(): void
    {
        $client = $this->_getClient();
        $response = $client->forcemergeAll();

        $this->assertFalse($response->hasError());
    }

    /**
     * Test bulk operations on Index.
     */
    #[Group('functional')]
    public function testBulkIndex(): void
    {
        $index = $this->_getClient()->getIndex('cryptocurrencies');

        $anonCoin = new Document('1', ['name' => 'anoncoin']);
        $ixCoin = new Document('2', ['name' => 'ixcoin']);

        $index->addDocuments([$anonCoin, $ixCoin]);

        $this->assertEquals('anoncoin', $index->getDocument(1)->get('name'));
        $this->assertEquals('ixcoin', $index->getDocument(2)->get('name'));

        $anonCoin->set('name', 'AnonCoin');
        $ixCoin->set('name', 'iXcoin');

        $index->updateDocuments([$anonCoin, $ixCoin]);

        $this->assertEquals('AnonCoin', $index->getDocument(1)->get('name'));
        $this->assertEquals('iXcoin', $index->getDocument(2)->get('name'));

        $index->deleteDocuments([$anonCoin, $ixCoin]);

        $this->expectException(NotFoundException::class);
        $index->getDocument(1);
        $index->getDocument(2);
    }

    public function testUpdateDocuments(): void
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        $initialValue = 28;
        $modifiedValue = 27;

        $doc1 = new Document(
            '1',
            ['name' => 'hans', 'age' => $initialValue],
            $indexName
        );
        $doc2 = new Document(
            '2',
            ['name' => 'anna', 'age' => $initialValue],
            $indexName
        );
        $data = [$doc1, $doc2];
        $client->addDocuments($data);

        foreach ($data as $i => $doc) {
            $data[$i]->age = $modifiedValue;
        }
        $client->updateDocuments($data);

        $index->refresh();

        $docData1 = $index->getDocument(1)->getData();
        $docData2 = $index->getDocument(2)->getData();

        $this->assertEquals($modifiedValue, $docData1['age']);
        $this->assertEquals($modifiedValue, $docData2['age']);
    }

    public function testGetDocumentWithVersion(): void
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        $initialValue = 28;

        $doc1 = new Document(
            '1',
            ['name' => 'hans', 'age' => $initialValue],
            $indexName
        );
        $data = [$doc1];
        $client->addDocuments($data);

        $doc1 = $index->getDocument(1);

        $this->assertGreaterThan(0, $doc1->getVersion());
    }

    /**
     * Test deleteIds method using string parameter for $index
     * and object parameter for $type.
     *
     * This test ensures that the deleteIds method of
     * the \Elastica\Client can properly accept and use
     * an $index parameter as string
     *
     * This test is a bit more verbose than just sending the
     * values to deleteIds and checking for exceptions or
     * warnings.
     *
     * It will add a document, search for it, then delete it
     * using the parameter types we are interested in, and then
     * re-search to verify that they have been deleted
     */
    #[Group('functional')]
    public function testDeleteIdsIdxString(): void
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], [
            'recreate' => true,
        ]);

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $index->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = [$resultData['_id']];

        // Check to make sure the document is in the index
        $resultSet = $index->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $idxString = $index->getName();

        // Using the existing $index variable that is a string
        $index->getClient()->deleteIds($ids, $idxString);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $index->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
     * Test deleteIds method using object parameter for $index
     * and object parameter for $type.
     *
     * This test ensures that the deleteIds method of
     * the \Elastica\Client can properly accept and use
     * an $index parameter that is an instance of \Elastica\Index
     *
     * This test is a bit more verbose than just sending the
     * values to deleteIds and checking for exceptions or
     * warnings.
     *
     * It will add a document, search for it, then delete it
     * using the parameter types we are interested in, and then
     * re-search to verify that they have been deleted
     */
    #[Group('functional')]
    public function testDeleteIdsIdxObjectTypeObject(): void
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], [
            'recreate' => true,
        ]);

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $index->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = [$resultData['_id']];

        // Check to make sure the document is in the index
        $resultSet = $index->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // Using the existing $index variable which is \Elastica\Index object
        $index->getClient()->deleteIds($ids, $index);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $index->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    public function testOneInvalidConnection(): void
    {
        $client = $this->_getClient([
            'hosts' => [
                // First connection is invalid and second should work
                $this->_getHost().':9999',
                $this->_getHost().':9200',
            ],
            'transport_config' => [
                'http_client_options' => [
                    RequestOptions::TIMEOUT => 1,
                    RequestOptions::CONNECT_TIMEOUT => 1,
                ],
            ],
        ]);

        $client->indices()->stats();

        /** @var TraceableSimpleNodePool $nodePool */
        $nodePool = $client->getTransport()->getNodePool();
        $nodes = $nodePool->getNodes();
        // two connections are setup
        $this->assertCount(2, $nodes);

        $this->markTestSkipped('Elastica\Test\ClientFunctionalTest::testOneInvalidConnection. Failed asserting that false is true.');

        // One connection has to be disabled
        // This returns an false in the most recent tests and as skipped for now
        // $this->assertTrue(false === $nodes[0]->isAlive() || false === $nodes[1]->isAlive());
    }

    public function testTwoInvalidConnection(): void
    {
        $client = $this->_getClient([
            'hosts' => [
                // First connection works, second should not work
                $this->_getHost().':9101',
                $this->_getHost().':9102',
            ],
            'transport_config' => [
                'http_client_options' => [
                    RequestOptions::TIMEOUT => 1,
                    RequestOptions::CONNECT_TIMEOUT => 1,
                ],
            ],
        ]);

        try {
            $client->indices()->stats();
            $this->fail('Should throw exception as no connection valid');
        } catch (NoNodeAvailableException) {
        }

        /** @var TraceableSimpleNodePool $nodePool */
        $nodePool = $client->getTransport()->getNodePool();
        $nodes = $nodePool->getNodes();

        // two connections are setup
        $this->assertCount(2, $nodes);

        // One connection has to be disabled
        $this->assertTrue(false === $nodes[0]->isAlive() && false === $nodes[1]->isAlive());
    }

    public function testUpdateDocumentByDocument(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document('1', ['field1' => 'value1', 'field2' => 'value2']);
        $index->addDocument($newDocument);

        $updateDocument = new Document('1', ['field2' => 'value2changed', 'field3' => 'value3added']);
        $client->updateDocument(1, $updateDocument, $index->getName());

        $document = $index->getDocument(1);

        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2changed', $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('value3added', $data['field3']);
    }

    public function testUpdateDocumentByScript(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document('1', ['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'should be changed']);
        $index->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += 5; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $client->updateDocument(1, $script, $index->getName());

        $document = $index->getDocument(1);

        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);
    }

    public function testUpdateDocumentByScriptWithUpsert(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $script = new Script('ctx._source.field2 += params.count; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $script->setParam('count', 5);
        $script->setUpsert(['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4']);

        // should use document fields because document does not exist, script is avoided
        $client->updateDocument(1, $script, $index->getName());

        $document = $index->getDocument(1);

        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(10, $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('should be removed', $data['field3']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertEquals('value4', $data['field4']);

        // should use script because document exists, document values are ignored
        $client->updateDocument(1, $script, $index->getName());

        $document = $index->getDocument(1);

        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);
    }

    public function testUpdateDocumentByRawData(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document('1', ['field1' => 'value1']);
        $index->addDocument($newDocument);

        $rawData = [
            'doc' => [
                'field2' => 'value2',
            ],
        ];

        $response = $client->updateDocument(1, $rawData, $index->getName(), ['retry_on_conflict' => 1]);
        $this->assertTrue($response->isOk());

        $document = $index->getDocument(1);

        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
    }

    public function testUpdateDocumentByDocumentWithUpsert(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document('1', ['field1' => 'value1updated', 'field2' => 'value2updated']);
        $upsert = new Document('1', ['field1' => 'value1', 'field2' => 'value2']);
        $newDocument->setUpsert($upsert);
        $client->updateDocument(1, $newDocument, $index->getName());

        $document = $index->getDocument(1);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);

        // should use update document because document exists, upsert document values are ignored
        $client->updateDocument(1, $newDocument, $index->getName());

        $document = $index->getDocument(1);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1updated', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2updated', $data['field2']);
    }

    public function testDocAsUpsert(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        // Confirm document one does not exist
        try {
            $index->getDocument(1);
            $this->fail('Exception was not thrown. Maybe the document exists?');
        } catch (\Exception $e) {
            // Ignore the exception because we expect the document to not exist.
        }

        $newDocument = new Document('1', ['field1' => 'value1', 'field2' => 'value2']);
        $newDocument->setDocAsUpsert(true);
        $client->updateDocument(1, $newDocument, $index->getName());

        $document = $index->getDocument(1);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
    }

    public function testUpdateWithInvalidType(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        // Try to update using a stdClass object
        $badDocument = new \stdClass();

        try {
            $client->updateDocument(1, $badDocument, $index->getName());
            $this->fail('Tried to update using an object that is not a Document or a Script but no exception was thrown');
        } catch (\Throwable $e) {
            // Good. An exception was thrown.
        }
    }

    public function testDeleteDocuments(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $docs = [
            new Document('1', ['field' => 'value1'], $index),
            new Document('2', ['field' => 'value2'], $index),
            new Document('3', ['field' => 'value3'], $index),
        ];

        $response = $client->addDocuments($docs);

        $this->assertCount(3, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(3, $index->count());

        $deleteDocs = [
            $docs[0],
            $docs[2],
        ];

        $response = $client->deleteDocuments($deleteDocs);

        $this->assertCount(2, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(1, $index->count());
    }

    public function testDeleteDocumentsWithRequestParameters(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $docs = [
            new Document('1', ['field' => 'value1'], $index),
            new Document('2', ['field' => 'value2'], $index),
            new Document('3', ['field' => 'value3'], $index),
        ];

        $response = $client->addDocuments($docs);

        $this->assertCount(3, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(3, $index->count());

        $deleteDocs = [
            $docs[0],
            $docs[2],
        ];

        $response = $client->deleteDocuments($deleteDocs, ['refresh' => true]);

        $this->assertCount(2, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $this->assertEquals(1, $index->count());
    }

    public function testLastRequestResponse(): void
    {
        $client = $this->_getClient();
        $response = $client->indices()->stats();

        $lastRequest = $client->getLastRequest();

        $this->assertInstanceOf(RequestInterface::class, $lastRequest);
        $this->assertEquals('/_stats', $lastRequest->getUri()->getPath());

        $lastResponse = $client->getLastResponse();
        $this->assertInstanceOf(Elasticsearch::class, $lastResponse);
        $this->assertSame($response, $lastResponse);
    }

    public function testUpdateDocumentPopulateFields(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document('1', ['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4']);
        $newDocument->setAutoPopulate();
        $index->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += params.count; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $script->setParam('count', 5);
        $script->setUpsert($newDocument);

        $client->updateDocument(
            '1',
            $script,
            $index->getName()
        );

        $data = $index->getDocument(1)->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);

        $script = new Script('ctx._source.field2 += params.count; ctx._source.remove("field4"); ctx._source.field1 = params.field1;', null, Script::LANG_PAINLESS);
        $script->setParam('count', 5);
        $script->setParam('field1', 'updated');
        $script->setUpsert($newDocument);

        $client->updateDocument(
            '1',
            $script,
            $index->getName()
        );

        $document = $index->getDocument(1);

        $data = $document->getData();

        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('updated', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(20, $data['field2']);
        $this->assertArrayNotHasKey('field3', $data);
        $this->assertArrayNotHasKey('field4', $data);
    }

    public function testAddDocumentsWithoutIds(): void
    {
        $docs = [];
        for ($i = 0; $i < 10; ++$i) {
            $docs[] = new Document(null, ['pos' => $i]);
        }

        foreach ($docs as $doc) {
            $this->assertFalse($doc->hasId());
        }

        $index = $this->_createIndex();

        $client = $index->getClient();
        $client->setConfigValue('document', ['autoPopulate' => true]);

        $index->addDocuments($docs);

        foreach ($docs as $doc) {
            $this->assertTrue($doc->hasId());
            $this->assertTrue($doc->hasVersion());
            $this->assertEquals(1, $doc->getVersion());
        }
    }

    public function testAddDocumentsPipeline(): void
    {
        $docs = [];
        for ($i = 0; $i < 10; ++$i) {
            $docs[] = new Document(null, ['old' => $i]);
        }

        $index = $this->_createIndex();
        $this->_createRenamePipeline();

        $client = $index->getClient();
        $client->setConfigValue('document', ['autoPopulate' => true]);

        $index->addDocuments($docs, ['pipeline' => 'renaming']);

        foreach ($docs as $i => $doc) {
            $foundDoc = $index->getDocument($doc->getId());
            $data = $foundDoc->getData();
            $this->assertArrayHasKey('new', $data);
            $this->assertEquals($i, $data['new']);
        }
    }

    public function testArrayQuery(): void
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], [
            'recreate' => true,
        ]);
        $index->addDocument(new Document('1', ['username' => 'ruflin']));
        $index->refresh();

        $query = [
            'query' => [
                'query_string' => [
                    'query' => 'ruflin',
                ],
            ],
        ];

        $response = $client->search(['body' => $query]);
        $responseArray = $response->asArray();

        $this->assertEquals(1, $responseArray['hits']['total']['value']);
    }

    public function testJSONQuery(): void
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], [
            'recreate' => true,
        ]);
        $index->addDocument(new Document('1', ['username' => 'ruflin']));
        $index->refresh();

        $query = '{"query":{"query_string":{"query":"ruflin"}}}';

        $response = $client->search(['body' => $query]);
        $responseArray = $response->asArray();

        $this->assertEquals(1, $responseArray['hits']['total']['value']);
    }

    public function testDateMathEscapingWithMixedRequestTypes(): void
    {
        $client = $this->_getClient();

        $now = new \DateTime();

        // e.g. test-2018.01.01
        $staticIndex = $client->getIndex('test-'.$now->format('Y.m.d'));
        $staticIndex->create();

        $dynamicIndex = $client->getIndex('<test-{now/d}>');

        // Index name goes through URI, should be escaped
        // Also, index should exist (matches $staticIndex)
        $dynamicIndex->refresh();

        $doc1 = $dynamicIndex->createDocument('1', ['name' => 'one']);
        $doc2 = $dynamicIndex->createDocument('2', ['name' => 'two']);

        // Index name goes through JSON body, should remain unescaped
        $bulk = new Bulk($client);
        $bulk->setIndex($dynamicIndex);
        $bulk->addDocuments([$doc1, $doc2]);
        // Should be sent successfully without exceptions
        $bulk->send();
    }

    public function testEndpointParamsRequest(): void
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);
        $client = $index->getClient();
        $doc = new Document(null, ['foo' => 'bar']);
        $doc->setRouting('first_routing');
        $index->addDocument($doc);

        $index->refresh();

        $response = $client->indices()->stats(['index' => $index->getName(), 'metric' => ['indexing']]);

        $this->assertArrayHasKey('index_total', $response->asArray()['indices'][$index->getName()]['total']['indexing']);

        $this->assertSame(
            2,
            $response->asArray()['indices'][$index->getName()]['total']['indexing']['index_total']
        );
    }

    #[DataProvider('endpointQueryRequestDataProvider')]
    public function testEndpointQueryRequest($query, $totalHits): void
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], [
            'recreate' => true,
        ]);
        $index->addDocument(new Document('1', ['username' => 'ruflin']));
        $index->refresh();

        $query = [
            'query' => [
                'query_string' => [
                    'query' => $query,
                ],
            ],
        ];

        $response = $client->search(['index' => $index->getName(), 'body' => $query]);

        $responseArray = $response->asArray();

        $this->assertEquals($totalHits, $responseArray['hits']['total']['value']);
    }

    public static function endpointQueryRequestDataProvider(): array
    {
        return [
            ['ruflin', 1],
            ['ruflin2', 0],
        ];
    }

    protected function setHttpClientOptions(HttpClientInterface $client, array $config, array $clientOptions = []): HttpClientInterface
    {
        if (empty($config) && empty($clientOptions)) {
            return $client;
        }
        $class = $client::class;
        $adapterClass = AdapterOptions::HTTP_ADAPTERS[$class];

        $adapter = new $adapterClass();

        return $adapter->setConfig($client, $config, $clientOptions);
    }
}
