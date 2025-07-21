<?php

declare(strict_types=1);

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\QueryBuilder\DSL;
use Elastica\Suggest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SuggestTest extends AbstractDSLTestCase
{
    #[Group('unit')]
    public function testType(): void
    {
        $suggestDSL = new DSL\Suggest();

        $this->assertInstanceOf(DSL::class, $suggestDSL);
        $this->assertEquals(DSL::TYPE_SUGGEST, $suggestDSL->getType());
    }

    #[Group('unit')]
    public function testInterface(): void
    {
        $suggestDSL = new DSL\Suggest();

        $this->_assertImplemented($suggestDSL, 'completion', Suggest\Completion::class, ['name', 'field']);
        $this->_assertImplemented($suggestDSL, 'phrase', Suggest\Phrase::class, ['name', 'field']);
        $this->_assertImplemented($suggestDSL, 'term', Suggest\Term::class, ['name', 'field']);
    }
}
