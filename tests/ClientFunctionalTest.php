<?php

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Bulk\ResponseSet;
use Elastica\Client;
use Elastica\Connection;
use Elastica\Document;
use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Request;
use Elastica\Response;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use Elasticsearch\Endpoints\Indices\Stats;
use Elasticsearch\Endpoints\Search;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * @group functional
 *
 * @internal
 */
class ClientFunctionalTest extends BaseTest
{
    public function testConnectionErrors(): void
    {
        $this->expectException(HttpException::class);

        $client = $this->_getClient(['host' => 'foo.bar', 'port' => '9201']);
        $client->getVersion();
    }

    public function testClientBadHost(): void
    {
        $this->expectException(HttpException::class);

        $client = $this->_getClient(['host' => 'localhost', 'port' => '9201']);
        $client->getVersion();
    }

    public function testClientBadHostWithTimeout(): void
    {
        $this->expectException(HttpException::class);

        $client = $this->_getClient(['host' => 'foo.bar', 'timeout' => 10]);
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
        $client = $this->_getClient(['connections' => [['host' => $this->_getHost(), 'port' => 9200]]]);
        $index = $client->getIndex('elastica_test1');
        $index->create([], true);

        // Adds 1 document to the index
        $doc1 = new Document(
            1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $index->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(
            2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(
            3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $index->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $resultSet = $index->search('rolf');
    }

    public function testTwoServersSame(): void
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient(['connections' => [
            ['host' => $this->_getHost(), 'port' => 9200],
            ['host' => $this->_getHost(), 'port' => 9200],
        ]]);
        $index = $client->getIndex('elastica_test1');
        $index->create([], true);

        // Adds 1 document to the index
        $doc1 = new Document(
            1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $index->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(
            2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(
            3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $index->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $resultSet = $index->search('rolf');
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
     *
     * @group functional
     */
    public function testBulkIndex(): void
    {
        $index = $this->_getClient()->getIndex('cryptocurrencies');

        $anonCoin = new Document(1, ['name' => 'anoncoin']);
        $ixCoin = new Document(2, ['name' => 'ixcoin']);

        $index->addDocuments([$anonCoin, $ixCoin]);

        $this->assertEquals('anoncoin', $index->getDocument(1)->get('name'));
        $this->assertEquals('ixcoin', $index->getDocument(2)->get('name'));

        $index->updateDocuments([
            new Document(1, ['name' => 'AnonCoin']),
            new Document(2, ['name' => 'iXcoin']),
        ]);

        $this->assertEquals('AnonCoin', $index->getDocument(1)->get('name'));
        $this->assertEquals('iXcoin', $index->getDocument(2)->get('name'));

        $ixCoin->setIndex(null);  // Make sure the index gets set properly if missing
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
            1,
            ['name' => 'hans', 'age' => $initialValue],
            $indexName
        );
        $doc2 = new Document(
            2,
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
     *
     * @group functional
     */
    public function testDeleteIdsIdxString(): void
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], true);

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
        $resp = $index->getClient()->deleteIds($ids, $idxString);

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
     *
     * @group functional
     */
    public function testDeleteIdsIdxObjectTypeObject(): void
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], true);

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
        $resp = $index->getClient()->deleteIds($ids, $index);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $index->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    public function testOneInvalidConnection(): void
    {
        $client = $this->_getClient();

        // First connection work, second should not work
        $connection1 = new Connection(['port' => '9100', 'timeout' => 2, 'host' => $this->_getHost()]);
        $connection2 = new Connection(['port' => '9200', 'timeout' => 2, 'host' => $this->_getHost()]);

        $client->setConnections([$connection1, $connection2]);

        $client->request('_stats', Request::GET);

        $connections = $client->getConnections();

        // two connections are setup
        $this->assertCount(2, $connections);

        // One connection has to be disabled
        $this->assertTrue(false === $connections[0]->isEnabled() || false === $connections[1]->isEnabled());
    }

    public function testTwoInvalidConnection(): void
    {
        $client = $this->_getClient();

        // First connection work, second should not work
        $connection1 = new Connection(['port' => '9101', 'timeout' => 2]);
        $connection2 = new Connection(['port' => '9102', 'timeout' => 2]);

        $client->setConnections([$connection1, $connection2]);

        try {
            $client->request('_stats', Request::GET);
            $this->fail('Should throw exception as no connection valid');
        } catch (HttpException $e) {
        }

        $connections = $client->getConnections();

        // two connections are setup
        $this->assertCount(2, $connections);

        // One connection has to be disabled
        $this->assertTrue(false === $connections[0]->isEnabled() || false === $connections[1]->isEnabled());
    }

    /**
     * Tests if the callback works in case a connection is down.
     */
    public function testCallback(): void
    {
        $count = 0;

        // Callback function which verifies that disabled connection objects are returned
        $callback = function (Connection $connection, \Exception $exception, Client $client) use (&$count): void {
            $this->assertInstanceOf(Connection::class, $connection);
            $this->assertInstanceOf(ConnectionException::class, $exception);
            $this->assertInstanceOf(Client::class, $client);
            $this->assertFalse($connection->isEnabled());
            ++$count;
        };

        $client = $this->_getClient([], $callback);

        // First connection work, second should not work
        $connection1 = new Connection(['port' => '9101', 'timeout' => 2]);
        $connection2 = new Connection(['port' => '9102', 'timeout' => 2]);

        $client->setConnections([$connection1, $connection2]);

        $this->assertEquals(0, $count);

        try {
            $client->request('_stats', Request::GET);
            $this->fail('Should throw exception as no connection valid');
        } catch (HttpException $e) {
            $this->assertTrue(true);
        }

        // Two disabled connections (from closure call)
        $this->assertEquals(2, $count);
    }

    public function testUrlConstructor(): void
    {
        $url = 'http://'.$this->_getHost().':9200/';

        // Url should overwrite invalid host
        $client = $this->_getClient(['url' => $url, 'port' => '9101', 'timeout' => 2]);

        $response = $client->request('_stats');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testUpdateDocumentByDocument(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 'value2']);
        $index->addDocument($newDocument);

        $updateDocument = new Document(1, ['field2' => 'value2changed', 'field3' => 'value3added']);
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

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'should be changed']);
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

        $this->assertInstanceOf(Document::class, $document);
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

        $this->assertInstanceOf(Document::class, $document);
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

        $newDocument = new Document(1, ['field1' => 'value1']);
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

        $newDocument = new Document(1, ['field1' => 'value1updated', 'field2' => 'value2updated']);
        $upsert = new Document(1, ['field1' => 'value1', 'field2' => 'value2']);
        $newDocument->setUpsert($upsert);
        $client->updateDocument(1, $newDocument, $index->getName());

        $document = $index->getDocument(1);
        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);

        // should use update document because document exists, upsert document values are ignored
        $client->updateDocument(1, $newDocument, $index->getName());

        $document = $index->getDocument(1);
        $this->assertInstanceOf(Document::class, $document);
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

        //Confirm document one does not exist
        try {
            $document = $index->getDocument(1);
            $this->fail('Exception was not thrown. Maybe the document exists?');
        } catch (\Exception $e) {
            //Ignore the exception because we expect the document to not exist.
        }

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 'value2']);
        $newDocument->setDocAsUpsert(true);
        $client->updateDocument(1, $newDocument, $index->getName());

        $document = $index->getDocument(1);
        $this->assertInstanceOf(Document::class, $document);
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

        //Try to update using a stdClass object
        $badDocument = new \stdClass();

        try {
            $client->updateDocument(1, $badDocument, $index->getName());
            $this->fail('Tried to update using an object that is not a Document or a Script but no exception was thrown');
        } catch (\Throwable $e) {
            //Good. An exception was thrown.
        }
    }

    public function testDeleteDocuments(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $docs = [
            new Document(1, ['field' => 'value1'], $index),
            new Document(2, ['field' => 'value2'], $index),
            new Document(3, ['field' => 'value3'], $index),
        ];

        $response = $client->addDocuments($docs);

        $this->assertInstanceOf(ResponseSet::class, $response);
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

        $this->assertInstanceOf(ResponseSet::class, $response);
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
            new Document(1, ['field' => 'value1'], $index),
            new Document(2, ['field' => 'value2'], $index),
            new Document(3, ['field' => 'value3'], $index),
        ];

        $response = $client->addDocuments($docs);

        $this->assertInstanceOf(ResponseSet::class, $response);
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

        $this->assertInstanceOf(ResponseSet::class, $response);
        $this->assertCount(2, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $this->assertEquals(1, $index->count());
    }

    public function testLastRequestResponse(): void
    {
        $client = $this->_getClient();
        $response = $client->request('_stats');

        $lastRequest = $client->getLastRequest();

        $this->assertInstanceOf(Request::class, $lastRequest);
        $this->assertEquals('_stats', $lastRequest->getPath());

        $lastResponse = $client->getLastResponse();
        $this->assertInstanceOf(Response::class, $lastResponse);
        $this->assertSame($response, $lastResponse);
    }

    public function testUpdateDocumentPopulateFields(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4']);
        $newDocument->setAutoPopulate();
        $index->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += params.count; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $script->setParam('count', 5);
        $script->setUpsert($newDocument);

        $client->updateDocument(
            1,
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
            1,
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
        $index->create([], true);
        $index->addDocument(new Document(1, ['username' => 'ruflin']));
        $index->refresh();

        $query = [
            'query' => [
                'query_string' => [
                    'query' => 'ruflin',
                ],
            ],
        ];

        $path = $index->getName().'/_search';

        $response = $client->request($path, Request::GET, $query);
        $responseArray = $response->getData();

        $this->assertEquals(1, $responseArray['hits']['total']['value']);
    }

    public function testJSONQuery(): void
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], true);
        $index->addDocument(new Document(1, ['username' => 'ruflin']));
        $index->refresh();

        $query = '{"query":{"query_string":{"query":"ruflin"}}}';

        $path = $index->getName().'/_search';

        $response = $client->request($path, Request::GET, $query);
        $responseArray = $response->getData();

        $this->assertEquals(1, $responseArray['hits']['total']['value']);
    }

    public function testLogger(): void
    {
        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->_getClient([], null, $logger);

        $logger->expects($this->once())
            ->method('debug')
            ->with(
                'Elastica Request',
                $this->logicalAnd(
                    $this->arrayHasKey('request'),
                    $this->arrayHasKey('response'),
                    $this->arrayHasKey('responseStatus')
                )
            )
        ;

        $client->request('_stats', Request::GET);
    }

    public function testLoggerOnFailure(): void
    {
        $this->expectException(HttpException::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->_getClient(['connections' => [
            ['host' => $this->_getHost(), 'port' => 9201],
        ]], null, $logger);

        $logger->expects($this->once())
            ->method('error')
            ->with(
                'Elastica Request Failure',
                $this->logicalAnd(
                    $this->arrayHasKey('exception'),
                    $this->arrayHasKey('request'),
                    $this->arrayHasKey('retry'),
                    $this->logicalNot($this->arrayHasKey('response'))
                )
            )
        ;

        $client->request('_stats', Request::GET);
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

        $doc1 = $dynamicIndex->createDocument(1, ['name' => 'one']);
        $doc2 = $dynamicIndex->createDocument(2, ['name' => 'two']);

        // Index name goes through JSON body, should remain unescaped
        $bulk = new Bulk($client);
        $bulk->setIndex($dynamicIndex);
        $bulk->addDocuments([$doc1, $doc2]);
        // Should be sent successfully without exceptions
        $bulk->send();
    }

    public function testDateMathEscapingWithEscapedPath(): void
    {
        $client = $this->_getClient();

        $now = new \DateTime();

        // e.g. test-2018.01.01
        $staticIndex = $client->getIndex('test-'.$now->format('Y.m.d'));
        $staticIndex->create();

        // It should not double escape the index name, since it came already escaped.
        $client->request('<test-{now%2Fd}>/_refresh');
    }

    public function testEndpointParamsRequest(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();
        $doc = new Document(null, ['foo' => 'bar']);
        $doc->setRouting('first_routing');
        $index->addDocument($doc);

        $index->refresh();

        $endpoint = new Stats();
        $endpoint->setIndex($index->getName());
        $endpoint->setMetric('indexing');
        $response = $client->requestEndpoint($endpoint);

        $this->assertArrayHasKey('index_total', $response->getData()['indices'][$index->getName()]['total']['indexing']);

        $this->assertSame(
            1,
            $response->getData()['indices'][$index->getName()]['total']['indexing']['index_total']
        );
    }

    /**
     * @dataProvider endpointQueryRequestDataProvider
     *
     * @param mixed $query
     * @param mixed $totalHits
     */
    public function testEndpointQueryRequest($query, $totalHits): void
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], true);
        $index->addDocument(new Document(1, ['username' => 'ruflin']));
        $index->refresh();

        $query = [
            'query' => [
                'query_string' => [
                    'query' => $query,
                ],
            ],
        ];

        $endpoint = new Search();
        $endpoint->setIndex($index->getName());
        $endpoint->setBody($query);

        $response = $client->requestEndpoint($endpoint);
        $responseArray = $response->getData();

        $this->assertEquals($totalHits, $responseArray['hits']['total']['value']);
    }

    public function endpointQueryRequestDataProvider(): array
    {
        return [
            ['ruflin', 1],
            ['ruflin2', 0],
        ];
    }
}
