<?php

declare(strict_types=1);

namespace Elastica\Test\Bulk\Action;

use Elastica\Bulk\Action\AbstractDocument;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
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
}
