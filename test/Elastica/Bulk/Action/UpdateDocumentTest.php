<?php
namespace Elastica\Test\BulkAction;

use Elastica\Bulk\Action\UpdateDocument;
use Elastica\Document;
use Elastica\Index;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class UpdateDocumentTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testUpdateDocument()
    {
        $document = new Document('', ['foo' => 'bar']);
        $action = new UpdateDocument($document);
        $this->assertEquals('update', $action->getOpType());
        $this->assertTrue($action->hasSource());

        $docExpected = '{"doc":{"foo":"bar"}}'."\n";
        $expected = '{"update":{}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());

        $action->setIndex('index');

        $expected = '{"update":{"_index":"index"}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());

        $action->setType('type');

        $expected = '{"update":{"_index":"index","_type":"type"}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());

        $action->setId(1);
        $expected = '{"update":{"_index":"index","_type":"type","_id":1}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());

        $action->setRouting(1);
        $expected = '{"update":{"_index":"index","_type":"type","_id":1,"_routing":1}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());

        $client = $this->_getClient();
        $index = new Index($client, 'index2');
        $type = new Type($index, 'type2');

        $action->setIndex($index);

        $expected = '{"update":{"_index":"index2","_type":"type","_id":1,"_routing":1}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());

        $action->setType($type);

        $expected = '{"update":{"_index":"index2","_type":"type2","_id":1,"_routing":1}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, $action->toString());
    }

    /**
     * @group unit
     */
    public function testUpdateDocumentAsUpsert()
    {
        $document = new Document(1, ['foo' => 'bar'], 'type', 'index');
        $document->setDocAsUpsert(true);
        $action = new UpdateDocument($document);

        $this->assertEquals('update', $action->getOpType());
        $this->assertTrue($action->hasSource());

        $expected = '{"update":{"_index":"index","_type":"type","_id":1}}'."\n"
                .'{"doc":{"foo":"bar"},"doc_as_upsert":true}'."\n";
        $this->assertEquals($expected, $action->toString());

        $document->setDocAsUpsert(1);
        $action->setDocument($document);
        $this->assertEquals($expected, $action->toString());

        $document->setDocAsUpsert(false);
        $action->setDocument($document);
        $expected = '{"update":{"_index":"index","_type":"type","_id":1}}'."\n"
                .'{"doc":{"foo":"bar"}}'."\n";
        $this->assertEquals($expected, $action->toString());

        $document->setDocAsUpsert(0);
        $action->setDocument($document);
        $this->assertEquals($expected, $action->toString());
    }
}
