<?php

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Bulk\Action;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotFoundException;
use Elastica\Test\Base as BaseTest;

class BulkTest extends BaseTest
{

    public function testSend()
    {
        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');
        $type2 = $index->getType('bulk_test2');
        $client = $index->getClient();

        $newDocument1 = $type->createDocument(1, array('name' => 'Mister Fantastic'));
        $newDocument2 = new Document(2, array('name' => 'Invisible Woman'));
        $newDocument3 = $type->createDocument(3, array('name' => 'The Human Torch'));
        $newDocument4 = $type->createDocument(null, array('name' => 'The Thing'));

        $newDocument1->setPercolate('*');
        $newDocument3->setOpType(Document::OP_TYPE_CREATE);

        $documents = array(
            $newDocument1,
            $newDocument2,
            $newDocument3,
            $newDocument4
        );

        $bulk = new Bulk($client);
        $bulk->setType($type2);
        $bulk->addDocuments($documents);

        $actions = $bulk->getActions();

        $this->assertInstanceOf('Elastica\Bulk\Action\IndexDocument', $actions[0]);
        $this->assertEquals('index', $actions[0]->getOpType());
        $this->assertSame($newDocument1, $actions[0]->getDocument());

        $this->assertInstanceOf('Elastica\Bulk\Action\IndexDocument', $actions[1]);
        $this->assertEquals('index', $actions[1]->getOpType());
        $this->assertSame($newDocument2, $actions[1]->getDocument());

        $this->assertInstanceOf('Elastica\Bulk\Action\CreateDocument', $actions[2]);
        $this->assertEquals('create', $actions[2]->getOpType());
        $this->assertSame($newDocument3, $actions[2]->getDocument());

        $this->assertInstanceOf('Elastica\Bulk\Action\IndexDocument', $actions[3]);
        $this->assertEquals('index', $actions[3]->getOpType());
        $this->assertSame($newDocument4, $actions[3]->getDocument());

        $data = $bulk->toArray();

        $expected = array(
            array('index' => array('_index' => 'elastica_test', '_type' => 'bulk_test', '_id' => 1, '_percolate' => '*')),
            array('name' => 'Mister Fantastic'),
            array('index' => array('_id' => 2)),
            array('name' => 'Invisible Woman'),
            array('create' => array('_index' => 'elastica_test', '_type' => 'bulk_test', '_id' => 3)),
            array('name' => 'The Human Torch'),
            array('index' => array('_index' => 'elastica_test', '_type' => 'bulk_test')),
            array('name' => 'The Thing'),
        );
        $this->assertEquals($expected, $data);

        $expected = '{"index":{"_index":"elastica_test","_type":"bulk_test","_id":1,"_percolate":"*"}}
{"name":"Mister Fantastic"}
{"index":{"_id":2}}
{"name":"Invisible Woman"}
{"create":{"_index":"elastica_test","_type":"bulk_test","_id":3}}
{"name":"The Human Torch"}
{"index":{"_index":"elastica_test","_type":"bulk_test"}}
{"name":"The Thing"}
';

        $this->assertEquals($expected, (string) $bulk);

        $response = $bulk->send();

        $this->assertInstanceOf('Elastica\Bulk\ResponseSet', $response);

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        foreach ($response as $i => $bulkResponse) {
            $this->assertInstanceOf('Elastica\Bulk\Response', $bulkResponse);
            $this->assertTrue($bulkResponse->isOk());
            $this->assertFalse($bulkResponse->hasError());
            $this->assertSame($actions[$i], $bulkResponse->getAction());
        }

        $type->getIndex()->refresh();
        $type2->getIndex()->refresh();

        $this->assertEquals(3, $type->count());
        $this->assertEquals(1, $type2->count());


        $bulk = new Bulk($client);
        $bulk->addDocument($newDocument3, Action::OP_TYPE_DELETE);

        $data = $bulk->toArray();

        $expected = array(
            array('delete' => array('_index' => 'elastica_test', '_type' => 'bulk_test', '_id' => 3)),
        );
        $this->assertEquals($expected, $data);

        $bulk->send();

        $type->getIndex()->refresh();

        $this->assertEquals(2, $type->count());

        try {
            $type->getDocument(3);
            $this->fail('Document #3 should be deleted');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSetIndexType()
    {
        $client = new Client();
        $index = $client->getIndex('index');
        $type = $index->getType('type');

        $index2 = $client->getIndex('index2');
        $type2 = $index2->getType('type2');

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
        $this->assertEquals('type2', $bulk->getType());

        $bulk->setType($type);
        $this->assertTrue($bulk->hasIndex());
        $this->assertTrue($bulk->hasType());
        $this->assertEquals('index', $bulk->getIndex());
        $this->assertEquals('type', $bulk->getType());

        $bulk->setIndex($index2);
        $this->assertTrue($bulk->hasIndex());
        $this->assertTrue($bulk->hasType());
        $this->assertEquals('index2', $bulk->getIndex());
        $this->assertEquals('type', $bulk->getType());
    }

    public function testAddActions()
    {
        $client = new Client();
        $bulk = new Bulk($client);

        $action1 = new Action(Action::OP_TYPE_DELETE);
        $action1->setIndex('index');
        $action1->setType('type');
        $action1->setId(1);

        $action2 = new Action(Action::OP_TYPE_INDEX);
        $action2->setIndex('index');
        $action2->setType('type');
        $action2->setId(1);
        $action2->setSource(array('name' => 'Batman'));

        $actions = array(
            $action1,
            $action2
        );

        $bulk->addActions($actions);

        $getActions = $bulk->getActions();

        $this->assertSame($action1, $getActions[0]);
        $this->assertSame($action2, $getActions[1]);
    }

    public function testAddRawData()
    {
        $bulk = new Bulk($this->_getClient());

        $rawData = array(
            array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
            array('user' => array('name' => 'hans')),
            array('delete' => array('_index' => 'test', '_type' => 'user', '_id' => '2')),
            array('delete' => array('_index' => 'test', '_type' => 'user', '_id' => '3')),
            array('create' => array('_index' => 'test', '_type' => 'user', '_id' => '4')),
            array('user' => array('name' => 'mans')),
            array('delete' => array('_index' => 'test', '_type' => 'user', '_id' => '5')),
        );

        $bulk->addRawData($rawData);

        $actions = $bulk->getActions();

        $this->assertInternalType('array', $actions);
        $this->assertEquals(5, count($actions));

        $this->assertInstanceOf('Elastica\Bulk\Action', $actions[0]);
        $this->assertEquals('index', $actions[0]->getOpType());
        $this->assertEquals($rawData[0]['index'], $actions[0]->getMetadata());
        $this->assertTrue($actions[0]->hasSource());
        $this->assertEquals($rawData[1], $actions[0]->getSource());

        $this->assertInstanceOf('Elastica\Bulk\Action', $actions[1]);
        $this->assertEquals('delete', $actions[1]->getOpType());
        $this->assertEquals($rawData[2]['delete'], $actions[1]->getMetadata());
        $this->assertFalse($actions[1]->hasSource());

        $this->assertInstanceOf('Elastica\Bulk\Action', $actions[2]);
        $this->assertEquals('delete', $actions[2]->getOpType());
        $this->assertEquals($rawData[3]['delete'], $actions[2]->getMetadata());
        $this->assertFalse($actions[2]->hasSource());

        $this->assertInstanceOf('Elastica\Bulk\Action', $actions[3]);
        $this->assertEquals('create', $actions[3]->getOpType());
        $this->assertEquals($rawData[4]['create'], $actions[3]->getMetadata());
        $this->assertTrue($actions[3]->hasSource());
        $this->assertEquals($rawData[5], $actions[3]->getSource());

        $this->assertInstanceOf('Elastica\Bulk\Action', $actions[4]);
        $this->assertEquals('delete', $actions[4]->getOpType());
        $this->assertEquals($rawData[6]['delete'], $actions[4]->getMetadata());
        $this->assertFalse($actions[4]->hasSource());
    }

    /**
     * @dataProvider invalidRawDataProvider
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidRawData($rawData, $failMessage)
    {
        $bulk = new Bulk($this->_getClient());

        $bulk->addRawData($rawData);

        $this->fail($failMessage);
    }

    public function invalidRawDataProvider()
    {
        return array(
            array(
                array(
                    array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
                    array('user' => array('name' => 'hans')),
                    array('user' => array('name' => 'mans')),
                ),
                'Two sources for one action'
            ),
            array(
                array(
                    array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
                    array('user' => array('name' => 'hans')),
                    array('upsert' => array('_index' => 'test', '_type' => 'user', '_id' => '2')),
                ),
                'Invalid optype for action'
            ),
            array(
                array(
                    array('user' => array('name' => 'mans')),
                ),
                'Source without action'
            ),
            array(
                array(
                    array(),
                ),
                'Empty array'
            ),
            array(
                array(
                    'dummy',
                ),
                'String as data'
            )
        );
    }

    public function testErrorRequest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');
        $client = $index->getClient();

        $documents = array(
            $type->createDocument(1, array('name' => 'Mister Fantastic')),
            $type->createDocument(2, array('name' => 'Invisible Woman')),
            $type->createDocument(2, array('name' => 'The Human Torch')),
        );

        $documents[2]->setOpType(Document::OP_TYPE_CREATE);

        $bulk = new Bulk($client);
        $bulk->addDocuments($documents);
        
        try {
            $bulk->send();
            $bulk->fail('3rd document create should produce error');
        } catch (ResponseException $e) {
            $this->assertContains('DocumentAlreadyExists', $e->getMessage());
            $failures = $e->getFailures();
            $this->assertInternalType('array', $failures);
            $this->assertArrayHasKey(0, $failures);
            $this->assertContains('DocumentAlreadyExists', $failures[0]);
        }
    }

    public function testRawDocumentDataRequest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');
        $client = $index->getClient();

        $documents = array(
            new Document(null, '{"name":"Mister Fantastic"}'),
            new Document(null, '{"name":"Invisible Woman"}'),
            new Document(null, '{"name":"The Human Torch"}'),
        );

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
        $this->assertEquals($expectedJson, $bulk->toString());

        $response = $bulk->send();
        $this->assertTrue($response->isOk());

        $type->getIndex()->refresh();

        $response = $type->search();
        $this->assertEquals(3, $response->count());

        foreach (array("Mister", "Invisible", "Torch") as $name) {
            $result = $type->search($name);
            $this->assertEquals(1, count($result->getResults()));
        }
    }

    /**
     * @dataProvider udpDataProvider
     */
    public function testUdp($clientConfig, $host, $port, $shouldFail = false)
    {
        $client = new Client($clientConfig);
        $index = $client->getIndex('elastica_test');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);
        $type = $index->getType('udp_test');
        $client = $index->getClient();

        $type->setMapping(array('name' => array('type' => 'string')));

        $docs = array(
            $type->createDocument(1, array('name' => 'Mister Fantastic')),
            $type->createDocument(2, array('name' => 'Invisible Woman')),
            $type->createDocument(3, array('name' => 'The Human Torch')),
            $type->createDocument(4, array('name' => 'The Thing')),
            $type->createDocument(5, array('name' => 'Mole Man')),
            $type->createDocument(6, array('name' => 'The Skrulls')),
        );

        $bulk = new Bulk($client);
        $bulk->addDocuments($docs);

        $bulk->sendUdp($host, $port);

        $i = 0;
        $limit = 20;
        do {
            usleep(200000);
        } while ($type->count() < 6 && ++$i < $limit);

        if ($shouldFail) {
            $this->assertEquals($limit, $i, 'Invalid udp connection data. Test should fail');
        } else {
            $this->assertLessThan($limit, $i, 'It took too much time waiting for UDP request result');

            foreach ($docs as $doc) {
                $getDoc = $type->getDocument($doc->getId());
                $this->assertEquals($doc->getData(), $getDoc->getData());
            }
        }
    }

    public function testUpdate()
    {
        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');
        $client = $index->getClient();

        $doc1 = $type->createDocument(1, array('name' => 'John'));
        $doc2 = $type->createDocument(2, array('name' => 'Paul'));
        $doc3 = $type->createDocument(3, array('name' => 'George'));
        $doc4 = $type->createDocument(4, array('name' => 'Ringo'));
        $documents = array($doc1, $doc2, $doc3, $doc4);

        //index some documents
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addDocuments($documents);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        //test updating via document
        $doc2 = $type->createDocument(2, array('name' => 'The Walrus'));
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $updateAction = new \Elastica\Bulk\Action\UpdateDocument($doc2);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();

        $doc = $type->getDocument(2);
        $docData = $doc->getData();
        $this->assertEquals('The Walrus', $docData['name']);

        //test updating via script
        $doc2 = new Document(2);
        $doc2->setScript(new \Elastica\Script('ctx._source.name += param1;', array('param1' => ' was Paul')));
        $updateAction = Action\AbstractDocument::create($doc2, Action::OP_TYPE_UPDATE);
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
        $doc = new Document(5, array('counter' => 1));
        $doc->setScript(new \Elastica\Script('ctx._scource.counter += count', array('count' => 1)));
        $updateAction = Action\AbstractDocument::create($doc, Action::OP_TYPE_UPDATE);
        $bulk = new Bulk($client);
        $bulk->setType($type);
        $bulk->addAction($updateAction);
        $response = $bulk->send();

        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());

        $index->refresh();
        $doc = $type->getDocument(5);
        $this->assertEquals(1, $doc->counter);

        $index->delete();
    }

    public function udpDataProvider()
    {
        return array(
            array(
                array(),
                null,
                null
            ),
            array(
                array(),
                'localhost',
                null
            ),
            array(
                array(),
                null,
                9700
            ),
            array(
                array(),
                'localhost',
                9700
            ),
            array(
                array(
                    'udp' => array(
                        'host' => 'localhost',
                        'port' => 9700,
                    )
                ),
                null,
                null
            ),
            array(
                array(
                    'udp' => array(
                        'host' => 'localhost',
                        'port' => 9800,
                    )
                ),
                'localhost',
                9700
            ),
            array(
                array(
                    'udp' => array(
                        'host' => 'localhost',
                        'port' => 9800,
                    )
                ),
                null,
                null,
                true
            ),
            array(
                array(
                ),
                'localhost',
                9800,
                true
            ),
        );
    }
}
