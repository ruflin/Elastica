<?php

declare(strict_types=1);

namespace Elastica\Test\Bulk\Action;

use Elastica\Bulk\Action\AbstractDocument;
use Elastica\Document;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class AbstractDocumentTest extends BaseTest
{
    #[Group('unit')]
    public function testCreateAbstractDocumentWithInvalidParameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The data needs to be a Document or a Script.');

        AbstractDocument::create(new \stdClass());
    }

    #[Group('unit')]
    public function testCreateAbstractDocumentWithScript(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Scripts can only be used with the update operation type.');

        AbstractDocument::create(new Script('foobar'), AbstractDocument::OP_TYPE_CREATE);
    }

    #[Group('unit')]
    #[DataProvider('concurrencyControlProvider')]
    public function testDocumentConcurrencyControlMetadata(string $opType, int $sequenceNumber): void
    {
        $document = new Document('1', ['foo' => 'bar'], 'index');
        $document->setSequenceNumber($sequenceNumber);
        $document->setPrimaryTerm(2);

        $action = AbstractDocument::create($document, $opType);

        $this->assertSame($opType, $action->getOpType());
        $this->assertSame(
            ['_id' => '1', '_index' => 'index', 'if_seq_no' => $sequenceNumber, 'if_primary_term' => 2],
            $action->getMetadata()
        );
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function concurrencyControlProvider(): iterable
    {
        foreach ([AbstractDocument::OP_TYPE_INDEX, AbstractDocument::OP_TYPE_CREATE, AbstractDocument::OP_TYPE_UPDATE, AbstractDocument::OP_TYPE_DELETE] as $opType) {
            yield $opType => [$opType, 5];
            // The first document in a shard has `_seq_no = 0`.
            yield $opType.' with sequence number 0' => [$opType, 0];
        }
    }

    #[Group('unit')]
    public function testScriptConcurrencyControlMetadata(): void
    {
        $script = new Script('ctx._source.foo = params.foo', ['foo' => 'bar'], Script::LANG_PAINLESS, '1');
        $script->setIndex('index');
        $script->setSequenceNumber(0);
        $script->setPrimaryTerm(2);

        $action = AbstractDocument::create($script);

        $this->assertSame(AbstractDocument::OP_TYPE_UPDATE, $action->getOpType());
        $this->assertSame(
            ['_id' => '1', '_index' => 'index', 'if_seq_no' => 0, 'if_primary_term' => 2],
            $action->getMetadata()
        );
    }

    #[Group('unit')]
    public function testDocumentWithoutConcurrencyControlMetadata(): void
    {
        $action = AbstractDocument::create(new Document('1', ['foo' => 'bar'], 'index'));

        $this->assertSame(['_id' => '1', '_index' => 'index'], $action->getMetadata());
    }
}
