<?php

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Bulk\ResponseSet;
use Elastica\Client;
use Elastica\Connection;
use Elastica\Document;
use Elastica\Exception\Connection\HttpException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Request;
use Elastica\Response;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elasticsearch\Endpoints\Get;
use Elasticsearch\Endpoints\Indices\Stats;
use Elasticsearch\Endpoints\Search;

class ClientTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $client = $this->_getClient();
        $this->assertCount(1, $client->getConnections());
    }

    /**
     * @group functional
     */
    public function testConnectionErrors()
    {
        $this->expectException(\Elastica\Exception\Connection\HttpException::class);

        $client = $this->_getClient(['host' => 'foo.bar', 'port' => '9201']);
        $client->getVersion();
    }

    /**
     * @group functional
     */
    public function testClientBadHost()
    {
        $this->expectException(\Elastica\Exception\Connection\HttpException::class);

        $client = $this->_getClient(['host' => 'localhost', 'port' => '9201']);
        $client->getVersion();
    }

    /**
     * @group functional
     */
    public function testClientBadHostWithtimeout()
    {
        $this->expectException(\Elastica\Exception\Connection\HttpException::class);

        $client = $this->_getClient(['host' => 'foo.bar', 'timeout' => 10]);
        $client->getVersion();
    }

    /**
     * @group functional
     */
    public function testGetVersion()
    {
        $client = $this->_getClient();
        $this->assertNotEmpty($client->getVersion());
        $this->assertTrue(\version_compare($client->getVersion(), $_SERVER['ELASTICSEARCH_VERSION'], '>='));
    }

    /**
     * @group functional
     */
    public function testConnectionsArray()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient(['connections' => [['host' => $this->_getHost(), 'port' => 9200]]]);
        $index = $client->getIndex('elastica_test1');
        $index->create([], true);

        $type = $index->getType('_doc');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $type->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $resultSet = $type->search('rolf');
    }

    /**
     * @group functional
     */
    public function testTwoServersSame()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient(['connections' => [
            ['host' => $this->_getHost(), 'port' => 9200],
            ['host' => $this->_getHost(), 'port' => 9200],
        ]]);
        $index = $client->getIndex('elastica_test1');
        $index->create([], true);

        $type = $index->getType('_doc');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $type->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $resultSet = $type->search('rolf');
    }

    /**
     * @group unit
     */
    public function testConnectionParamsArePreparedForConnectionsOption()
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['connections' => [['url' => $url]]]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    /**
     * @group unit
     */
    public function testConnectionParamsArePreparedForServersOption()
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['servers' => [['url' => $url]]]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    /**
     * @group unit
     */
    public function testConnectionParamsArePreparedForDefaultOptions()
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['url' => $url]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    /**
     * @group functional
     */
    public function testBulk()
    {
        $client = $this->_getClient();

        $params = [
            ['index' => ['_index' => 'test', '_type' => '_doc', '_id' => '1']],
            ['user' => ['name' => 'hans']],
            ['index' => ['_index' => 'test', '_type' => '_doc', '_id' => '2']],
            ['user' => ['name' => 'peter']],
        ];

        $client->bulk($params);
    }

    /**
     * @group functional
     */
    public function testForcemergeAll()
    {
        $client = $this->_getClient();
        $response = $client->forcemergeAll();

        $this->assertFalse($response->hasError());
    }

    /**
     * @group unit
     */
    public function testAddDocumentsEmpty()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        $client->addDocuments([]);
    }

    /**
     * Test bulk operations on Index.
     *
     * @group functional
     */
    public function testBulkIndex()
    {
        $index = $this->_getClient()->getIndex('cryptocurrencies');

        $anonCoin = new Document(1, ['name' => 'anoncoin'], '_doc');
        $ixCoin = new Document(2, ['name' => 'ixcoin'], '_doc');

        $index->addDocuments([$anonCoin, $ixCoin]);

        $this->assertEquals('anoncoin', $index->getType('_doc')->getDocument(1)->get('name'));
        $this->assertEquals('ixcoin', $index->getType('_doc')->getDocument(2)->get('name'));

        $index->updateDocuments([
            new Document(1, ['name' => 'AnonCoin'], '_doc'),
            new Document(2, ['name' => 'iXcoin'], '_doc'),
        ]);

        $this->assertEquals('AnonCoin', $index->getType('_doc')->getDocument(1)->get('name'));
        $this->assertEquals('iXcoin', $index->getType('_doc')->getDocument(2)->get('name'));

        $ixCoin->setIndex(null);  // Make sure the index gets set properly if missing
        $index->deleteDocuments([$anonCoin, $ixCoin]);

        $this->expectException(NotFoundException::class);
        $index->getType('_doc')->getDocument(1);
        $index->getType('_doc')->getDocument(2);
    }

    /**
     * Test bulk operations on Type.
     *
     * @group functional
     */
    public function testBulkType()
    {
        $type = $this->_getClient()->getIndex('cryptocurrencies')->getType('_doc');

        $liteCoin = new Document(1, ['name' => 'litecoin']);
        $nameCoin = new Document(2, ['name' => 'namecoin']);

        $type->addDocuments([$liteCoin, $nameCoin]);

        $this->assertEquals('litecoin', $type->getDocument(1)->get('name'));
        $this->assertEquals('namecoin', $type->getDocument(2)->get('name'));

        $type->updateDocuments([
            new Document(1, ['name' => 'LiteCoin']),
            new Document(2, ['name' => 'NameCoin']),
        ]);

        $this->assertEquals('LiteCoin', $type->getDocument(1)->get('name'));
        $this->assertEquals('NameCoin', $type->getDocument(2)->get('name'));

        $nameCoin->setType(null);  // Make sure the type gets set properly if missing
        $type->deleteDocuments([$liteCoin, $nameCoin]);

        $this->expectException(NotFoundException::class);
        $type->getDocument(1);
        $type->getDocument(2);
    }

    /**
     * @group functional
     */
    public function testUpdateDocuments()
    {
        $indexName = 'test';
        $typeName = '_doc';

        $client = $this->_getClient();
        $type = $client->getIndex($indexName)->getType($typeName);

        $initialValue = 28;
        $modifiedValue = 27;

        $doc1 = new Document(
            1,
            ['name' => 'hans', 'age' => $initialValue],
            $typeName,
            $indexName
        );
        $doc2 = new Document(
            2,
            ['name' => 'anna', 'age' => $initialValue],
            $typeName,
            $indexName
        );
        $data = [$doc1, $doc2];
        $client->addDocuments($data);

        foreach ($data as $i => $doc) {
            $data[$i]->age = $modifiedValue;
        }
        $client->updateDocuments($data);

        $docData1 = $type->getDocument(1)->getData();
        $docData2 = $type->getDocument(2)->getData();

        $this->assertEquals($modifiedValue, $docData1['age']);
        $this->assertEquals($modifiedValue, $docData2['age']);
    }

    /**
     * Test deleteIds method using string parameters.
     *
     * This test ensures that the deleteIds method of
     * the \Elastica\Client can properly accept and use
     * an $index parameter and $type parameter that are
     * strings
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
    public function testDeleteIdsIdxStringTypeString()
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex(null, true, 2);

        $type = $index->getType('_doc');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $doc->setRouting('first_routing');
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = [$resultData['_id']];

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $idxString = $index->getName();
        $typeString = $type->getName();
        $this->assertInternalType('string', $idxString);
        $this->assertInternalType('string', $typeString);

        // Try to delete doc with a routing value which hashes to
        // a different shard then the id.
        $resp = $index->getClient()->deleteIds($ids, $index, $type, 'second_routing');

        // Refresh the index
        $index->refresh();

        // Research the index to verify that the items are still there
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type, 'first_routing');

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
     * Test deleteIds method using string parameter for $index
     * and object parameter for $type.
     *
     * This test ensures that the deleteIds method of
     * the \Elastica\Client can properly accept and use
     * an $index parameter that is a string and a $type
     * parameter that is of type \Elastica\Type
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
    public function testDeleteIdsIdxStringTypeObject()
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], true);
        $type = $index->getType('_doc');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = [$resultData['_id']];

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $idxString = $index->getName();
        $this->assertInternalType('string', $idxString);
        $this->assertInstanceOf(Type::class, $type);

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
     * Test deleteIds method using object parameter for $index
     * and string parameter for $type.
     *
     * This test ensures that the deleteIds method of
     * the \Elastica\Client can properly accept and use
     * an $index parameter that is  of type Elasitca_Index
     * and a $type parameter that is a string
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
    public function testDeleteIdsIdxObjectTypeString()
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], true);
        $type = $index->getType('_doc');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = [$resultData['_id']];

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $typeString = $type->getName();
        $this->assertInstanceOf(Index::class, $index);
        $this->assertInternalType('string', $typeString);

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
     * Test deleteIds method using object parameter for $index
     * and object parameter for $type.
     *
     * This test ensures that the deleteIds method of
     * the \Elastica\Client can properly accept and use
     * an $index parameter that is an object and a $type
     * parameter that is of type \Elastica\Type
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
    public function testDeleteIdsIdxObjectTypeObject()
    {
        $data = ['username' => 'hans'];
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create([], true);
        $type = $index->getType('_doc');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = [$resultData['_id']];

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $this->assertInstanceOf(Index::class, $index);
        $this->assertInstanceOf(Type::class, $type);

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
     * @group functional
     */
    public function testOneInvalidConnection()
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
        $this->assertTrue(false == $connections[0]->isEnabled() || false == $connections[1]->isEnabled());
    }

    /**
     * @group functional
     */
    public function testTwoInvalidConnection()
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
        $this->assertTrue(false == $connections[0]->isEnabled() || false == $connections[1]->isEnabled());
    }

    /**
     * Tests if the callback works in case a connection is down.
     *
     * @group functional
     */
    public function testCallback()
    {
        $count = 0;
        $object = $this;

        // Callback function which verifies that disabled connection objects are returned
        $callback = function ($connection, $exception, $client) use (&$object, &$count) {
            $object->assertInstanceOf(Connection::class, $connection);
            $object->assertInstanceOf(ConnectionException::class, $exception);
            $object->assertInstanceOf(Client::class, $client);
            $object->assertFalse($connection->isEnabled());
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

    /**
     * @group functional
     */
    public function testUrlConstructor()
    {
        $url = 'http://'.$this->_getHost().':9200/';

        // Url should overwrite invalid host
        $client = $this->_getClient(['url' => $url, 'port' => '9101', 'timeout' => 2]);

        $response = $client->request('_stats');
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentByDocument()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 'value2']);
        $type->addDocument($newDocument);

        $updateDocument = new Document(1, ['field2' => 'value2changed', 'field3' => 'value3added']);
        $client->updateDocument(1, $updateDocument, $index->getName(), $type->getName());

        $document = $type->getDocument(1);

        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2changed', $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('value3added', $data['field3']);
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentByScript()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'should be changed']);
        $type->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += 5; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $client->updateDocument(1, $script, $index->getName(), $type->getName());

        $document = $type->getDocument(1);

        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentByScriptWithUpsert()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $script = new Script('ctx._source.field2 += params.count; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $script->setParam('count', 5);
        $script->setUpsert(['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4']);

        // should use document fields because document does not exist, script is avoided
        $client->updateDocument(1, $script, $index->getName(), $type->getName());

        $document = $type->getDocument(1);

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
        $client->updateDocument(1, $script, $index->getName(), $type->getName());

        $document = $type->getDocument(1);

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

    /**
     * @group functional
     */
    public function testUpdateDocumentByRawData()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1']);
        $type->addDocument($newDocument);

        $rawData = [
            'doc' => [
                'field2' => 'value2',
            ],
        ];

        $response = $client->updateDocument(1, $rawData, $index->getName(), $type->getName(), ['retry_on_conflict' => 1]);
        $this->assertTrue($response->isOk());

        $document = $type->getDocument(1);

        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentByDocumentWithUpsert()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1updated', 'field2' => 'value2updated']);
        $upsert = new Document(1, ['field1' => 'value1', 'field2' => 'value2']);
        $newDocument->setUpsert($upsert);
        $client->updateDocument(1, $newDocument, $index->getName(), $type->getName());

        $document = $type->getDocument(1);
        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);

        // should use update document because document exists, upsert document values are ignored
        $client->updateDocument(1, $newDocument, $index->getName(), $type->getName());

        $document = $type->getDocument(1);
        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1updated', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2updated', $data['field2']);
    }

    /**
     * @group functional
     */
    public function testDocAsUpsert()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        //Confirm document one does not exist
        try {
            $document = $type->getDocument(1);
            $this->fail('Exception was not thrown. Maybe the document exists?');
        } catch (\Exception $e) {
            //Ignore the exception because we expect the document to not exist.
        }

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 'value2']);
        $newDocument->setDocAsUpsert(true);
        $client->updateDocument(1, $newDocument, $index->getName(), $type->getName());

        $document = $type->getDocument(1);
        $this->assertInstanceOf(Document::class, $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
    }

    /**
     * @group functional
     */
    public function testUpdateWithInvalidType()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        //Try to update using a stdClass object
        $badDocument = new \stdClass();

        try {
            $client->updateDocument(1, $badDocument, $index->getName(), $type->getName());
            $this->fail('Tried to update using an object that is not a Document or a Script but no exception was thrown');
        } catch (\Exception $e) {
            //Good. An exception was thrown.
        }
    }

    /**
     * @group functional
     */
    public function testDeleteDocuments()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $docs = [
            new Document(1, ['field' => 'value1'], $type, $index),
            new Document(2, ['field' => 'value2'], $type, $index),
            new Document(3, ['field' => 'value3'], $type, $index),
        ];

        $response = $client->addDocuments($docs);

        $this->assertInstanceOf(ResponseSet::class, $response);
        $this->assertCount(3, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(3, $type->count());

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

        $this->assertEquals(1, $type->count());
    }

    /**
     * @group functional
     */
    public function testDeleteDocumentsWithRequestParameters()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $docs = [
            new Document(1, ['field' => 'value1'], $type, $index),
            new Document(2, ['field' => 'value2'], $type, $index),
            new Document(3, ['field' => 'value3'], $type, $index),
        ];

        $response = $client->addDocuments($docs);

        $this->assertInstanceOf(ResponseSet::class, $response);
        $this->assertCount(3, $response);
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(3, $type->count());

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

        $this->assertEquals(1, $type->count());
    }

    /**
     * @group functional
     */
    public function testLastRequestResponse()
    {
        $client = $this->_getClient();
        $response = $client->request('_stats');

        $this->assertInstanceOf(Response::class, $response);

        $lastRequest = $client->getLastRequest();

        $this->assertInstanceOf(Request::class, $lastRequest);
        $this->assertEquals('_stats', $lastRequest->getPath());

        $lastResponse = $client->getLastResponse();
        $this->assertInstanceOf(Response::class, $lastResponse);
        $this->assertSame($response, $lastResponse);
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentPopulateFields()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');
        $client = $index->getClient();

        $newDocument = new Document(1, ['field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4']);
        $newDocument->setAutoPopulate();
        $type->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += params.count; ctx._source.remove("field3"); ctx._source.field4 = "changed"', null, Script::LANG_PAINLESS);
        $script->setParam('count', 5);
        $script->setUpsert($newDocument);

        $client->updateDocument(
            1,
            $script,
            $index->getName(),
            $type->getName()
        );

        $data = $type->getDocument(1)->getData();
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
            $index->getName(),
            $type->getName()
        );

        $document = $type->getDocument(1);

        $data = $document->getData();

        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('updated', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(20, $data['field2']);
        $this->assertArrayNotHasKey('field3', $data);
        $this->assertArrayNotHasKey('field4', $data);
    }

    /**
     * @group functional
     */
    public function testAddDocumentsWithoutIds()
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

        $type = $index->getType('_doc');
        $type->addDocuments($docs);

        foreach ($docs as $doc) {
            $this->assertTrue($doc->hasId());
            $this->assertTrue($doc->hasVersion());
            $this->assertEquals(1, $doc->getVersion());
        }
    }

    /**
     * @group functional
     */
    public function testAddDocumentsPipeline()
    {
        $docs = [];
        for ($i = 0; $i < 10; ++$i) {
            $docs[] = new Document(null, ['old' => $i]);
        }

        $index = $this->_createIndex();
        $this->_createRenamePipeline();

        $client = $index->getClient();
        $client->setConfigValue('document', ['autoPopulate' => true]);

        $type = $index->getType('_doc');
        $type->addDocuments($docs, ['pipeline' => 'renaming']);

        foreach ($docs as $i => $doc) {
            $foundDoc = $type->getDocument($doc->getId());
            $this->assertInstanceOf(Document::class, $foundDoc);
            $data = $foundDoc->getData();
            $this->assertArrayHasKey('new', $data);
            $this->assertEquals($i, $data['new']);
        }
    }

    /**
     * @group unit
     */
    public function testConfigValue()
    {
        $config = [
            'level1' => [
                'level2' => [
                    'level3' => 'value3',
                ],
                'level21' => 'value21',
            ],
            'level11' => 'value11',
        ];
        $client = $this->_getClient($config);

        $this->assertNull($client->getConfigValue('level12'));
        $this->assertFalse($client->getConfigValue('level12', false));
        $this->assertEquals(10, $client->getConfigValue('level12', 10));

        $this->assertEquals('value11', $client->getConfigValue('level11'));
        $this->assertNotNull($client->getConfigValue('level11'));
        $this->assertNotEquals(false, $client->getConfigValue('level11', false));
        $this->assertNotEquals(10, $client->getConfigValue('level11', 10));

        $this->assertEquals('value3', $client->getConfigValue(['level1', 'level2', 'level3']));
        $this->assertInternalType('array', $client->getConfigValue(['level1', 'level2']));
    }

    /**
     * @group functional
     */
    public function testArrayQuery()
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');
        $type->addDocument(new Document(1, ['username' => 'ruflin']));
        $index->refresh();

        $query = [
            'query' => [
                'query_string' => [
                    'query' => 'ruflin',
                ],
            ],
        ];

        $path = $index->getName().'/'.$type->getName().'/_search';

        $response = $client->request($path, Request::GET, $query);
        $responseArray = $response->getData();

        $this->assertEquals(1, $responseArray['hits']['total']['value']);
    }

    /**
     * @group functional
     */
    public function testJSONQuery()
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');
        $type->addDocument(new Document(1, ['username' => 'ruflin']));
        $index->refresh();

        $query = '{"query":{"query_string":{"query":"ruflin"}}}';

        $path = $index->getName().'/'.$type->getName().'/_search';

        $response = $client->request($path, Request::GET, $query);
        $responseArray = $response->getData();

        $this->assertEquals(1, $responseArray['hits']['total']['value']);
    }

    /**
     * @group unit
     */
    public function testAddHeader()
    {
        $client = $this->_getClient();

        // add one header
        $client->addHeader('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $client->getConfigValue('headers'));

        // check class
        $this->assertInstanceOf(Client::class, $client->addHeader('foo', 'bar'));

        // check invalid parameters
        try {
            $client->addHeader(new \stdClass(), 'foo');
            $this->fail('Header name is not a string but exception not thrown');
        } catch (InvalidException $ex) {
        }

        try {
            $client->addHeader('foo', new \stdClass());
            $this->fail('Header value is not a string but exception not thrown');
        } catch (InvalidException $ex) {
        }
    }

    /**
     * @group unit
     */
    public function testRemoveHeader()
    {
        $client = $this->_getClient();

        // set headers
        $headers = [
            'first' => 'first value',
            'second' => 'second value',
        ];
        foreach ($headers as $key => $value) {
            $client->addHeader($key, $value);
        }
        $this->assertEquals($headers, $client->getConfigValue('headers'));

        // remove one
        $client->removeHeader('first');
        unset($headers['first']);
        $this->assertEquals($headers, $client->getConfigValue('headers'));

        // check class
        $this->assertInstanceOf(Client::class, $client->removeHeader('second'));

        // check invalid parameter
        try {
            $client->removeHeader(new \stdClass());
            $this->fail('Header name is not a string but exception not thrown');
        } catch (InvalidException $ex) {
        }
    }

    /**
     * @group unit
     */
    public function testPassBigIntSettingsToConnectionConfig()
    {
        $client = new Client(['bigintConversion' => true]);

        $this->assertTrue($client->getConnection()->getConfig('bigintConversion'));
    }

    /**
     * @group unit
     */
    public function testClientConnectWithConfigSetByMethod()
    {
        $client = new Client();
        $client->setConfigValue('host', $this->_getHost());
        $client->setConfigValue('port', $this->_getPort());

        $client->connect();
        $this->assertTrue($client->hasConnection());

        $connection = $client->getConnection();
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals($this->_getHost(), $connection->getHost());
        $this->assertEquals($this->_getPort(), $connection->getPort());
    }

    /**
     * @group functional
     */
    public function testLogger()
    {
        $logger = $this->createMock('Psr\\Log\\LoggerInterface');
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
            );

        $client->request('_stats', Request::GET);
    }

    /**
     * @group functional
     */
    public function testLoggerOnFailure()
    {
        $this->expectException(\Elastica\Exception\Connection\HttpException::class);

        $logger = $this->createMock('Psr\\Log\\LoggerInterface');
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
            );

        $client->request('_stats', Request::GET);
    }

    /**
     * @group functional
     */
    public function testDateMathEscapingWithMixedRequestTypes()
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

        $type = $dynamicIndex->getType('_doc');
        $doc1 = $type->createDocument(1, ['name' => 'one']);
        $doc2 = $type->createDocument(2, ['name' => 'two']);

        // Index name goes through JSON body, should remain unescaped
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments([$doc1, $doc2]);
        // Should be sent successfully without exceptions
        $bulk->send();
    }

    /**
     * @group functional
     */
    public function testDateMathEscapingWithEscapedPath()
    {
        $client = $this->_getClient();

        $now = new \DateTime();

        // e.g. test-2018.01.01
        $staticIndex = $client->getIndex('test-'.$now->format('Y.m.d'));
        $staticIndex->create();

        // It should not double escape the index name, since it came already escaped.
        $client->request('<test-{now%2Fd}>/_refresh');
    }

    /**
     * @group functional
     */
    public function testEndpointParamsRequest()
    {
        $index = $this->_createIndex();
        $client = $index->getClient();
        $type = $index->getType('_doc');
        $doc = new Document(null, ['foo' => 'bar']);
        $doc->setRouting('first_routing');
        $type->addDocument($doc);

        $index->refresh();

        $endpoint = new Stats();
        $endpoint->setIndex($index->getName());
        $endpoint->setMetric('indexing');
        $endpoint->setParams(['types' => [$type->getName()]]);
        $response = $client->requestEndpoint($endpoint);

        $this->assertArrayHasKey('types', $response->getData()['indices'][$index->getName()]['total']['indexing']);

        $this->assertEquals(
            ['_doc'],
            \array_keys($response->getData()['indices'][$index->getName()]['total']['indexing']['types'])
        );
    }

    /**
     * @group functional
     * @dataProvider endpointQueryRequestDataProvider
     */
    public function testEndpointQueryRequest($query, $totalHits)
    {
        $client = $this->_getClient();

        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');
        $type->addDocument(new Document(1, ['username' => 'ruflin']));
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
        $endpoint->setType($type->getName());
        $endpoint->setBody($query);

        $response = $client->requestEndpoint($endpoint);
        $responseArray = $response->getData();

        $this->assertEquals($totalHits, $responseArray['hits']['total']['value']);
    }

    public function endpointQueryRequestDataProvider()
    {
        return [
            ['ruflin', 1],
            ['ruflin2', 0],
        ];
    }
}
