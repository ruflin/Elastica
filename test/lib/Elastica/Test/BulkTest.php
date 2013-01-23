<?php

namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Test\Base as BaseTest;

class BulkTest extends BaseTest
{
    public function testIndexDocuments()
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
        $bulk->setData($documents);

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


        $bulk->send();

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
}