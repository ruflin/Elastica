<?php

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException;
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
        $bulk->addDocument($newDocument3, Document::OP_TYPE_DELETE);

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

        $documents[2]->setopType(Document::OP_TYPE_CREATE);

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