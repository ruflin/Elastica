<?php

declare(strict_types=1);

namespace Elastica\Test\Bulk\Action;

use Elastica\Bulk\Action\UpdateDocument;
use Elastica\Document;
use Elastica\Index;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class UpdateDocumentTest extends BaseTest
{
    #[Group('unit')]
    public function testUpdateDocument(): void
    {
        $document = new Document(null, ['foo' => 'bar']);
        $action = new UpdateDocument($document);
        $this->assertEquals('update', $action->getOpType());
        $this->assertTrue($action->hasSource());

        $docExpected = '{"doc":{"foo":"bar"}}'."\n";
        $expected = '{"update":{}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, (string) $action);

        $action->setIndex('index');

        $expected = '{"update":{"_index":"index"}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, (string) $action);

        $action->setId('1');
        $expected = '{"update":{"_index":"index","_id":"1"}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, (string) $action);

        $action->setRouting(1);
        $expected = '{"update":{"_index":"index","_id":"1","routing":1}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, (string) $action);

        $client = $this->_getClient();
        $index = new Index($client, 'index2');

        $action->setIndex($index);

        $expected = '{"update":{"_index":"index2","_id":"1","routing":1}}'."\n";
        $expected .= $docExpected;
        $this->assertEquals($expected, (string) $action);
    }

    #[Group('unit')]
    public function testUpdateDocumentAsUpsert(): void
    {
        $document = (new Document('1', ['foo' => 'bar'], 'index'))
            ->setDocAsUpsert(true)
        ;
        $action = new UpdateDocument($document);

        $this->assertSame('update', $action->getOpType());
        $this->assertTrue($action->hasSource());

        $expected = <<<'JSON'
            {"update":{"_id":"1","_index":"index"}}
            {"doc":{"foo":"bar"},"doc_as_upsert":true}
            JSON;

        $this->assertSame($expected, \trim((string) $action));

        $document->setDocAsUpsert(true);
        $action->setDocument($document);
        $this->assertSame($expected, \trim((string) $action));

        $document->setDocAsUpsert(false);
        $action->setDocument($document);

        $expected = <<<'JSON'
            {"update":{"_id":"1","_index":"index"}}
            {"doc":{"foo":"bar"}}
            JSON;

        $this->assertSame($expected, \trim((string) $action));

        $document->setDocAsUpsert(false);
        $action->setDocument($document);
        $this->assertSame($expected, \trim((string) $action));
    }
}
